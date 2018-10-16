<?php

require_once 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use SendGrid\Email;
use SendGrid\Content;
use SendGrid\Mail;
use SendGrid\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PhpAmqpLib\Message\AMQPMessage;

class BaseWorker
{
    // constants for worker type
    const SUBMISSION_WORKER  = 1;
    const EVALUATION_WORKER  = 2;

    // constants for environment variables
    const MSG_QUEUING_URL_ENV_VAR   = 'CLOUDAMQP_URL';
    const DATABASE_URL_ENV_VAR      = 'DATABASE_URL';
    const SENDGRID_API_KEY_ENV_VAR  = 'SENDGRID_API_KEY';

    // constants for email
    const EMAIL_TO_MENTOR = 1;
    const EMAIL_TO_MENTEE = 2;

    const EMAIL_URL =
        'https://demo-node-henz00.herokuapp.com/account/view-login';

    // class attributes
    protected $amqpConn;
    protected $mysqlConn;
    protected $sendGridConn;
    protected $queue;
    protected $dbName;
    protected $workerType;
    protected $log;

    /**
     * BaseWorker constructor.
     * @param string $queue
     * @param int $workerType
     */
    public function __construct($queue, $workerType)
    {
        $this->queue = $queue;
        $this->workerType = $workerType;

        // create a log channel
        $this->log = new Logger('default');
        $this->log->pushHandler(
            new StreamHandler(
                'php://stderr',
                Logger::INFO
            )
        );
        $this->connectToRabbitMq();
        $this->connectToSendGrid();
    }

    /**
     * Main function of worker
     */
    public function start()
    {
        $this->log->addInfo(
            'Starting worker'
        );
        // main function of the worker
        try {
            $channel = $this->amqpConn->channel();
            $channel->queue_declare(
                $this->queue,
                false,
                false,
                false,
                false
            );
            $this->log->addInfo(
                ' [*] Waiting for messages. To exit press CTRL+C'
            );

            $channel->basic_consume(
                $this->queue,
                '',
                false,
                true,
                false,
                false,
                array($this, 'callback')
            );
            while(count($channel->callbacks)) {
                $channel->wait();
            }
        } catch (Exception $e){
            $this->printException($e);
        } finally {
            $this->amqpConn = null;
            $this->mysqlConn = null;
        }
    }

    /**
     * Callback function for RabbitMQ
     * @param AMQPMessage $msg
     */
    public function callback($msg)
    {
        $this->log->addInfo(
            " [x] Received: $msg->body"
        );

        // decode RabbitMQ message
        $msgDecoded = json_decode($msg->body);
        $phaseId = $msgDecoded->phaseId;
        $senderAccountId = $msgDecoded->senderAccountId;
        $receiverAccountId = $msgDecoded->receiverAccountId;

        $this->sendEmail(
            $senderAccountId,
            $receiverAccountId,
            $phaseId,
            $this->workerType
        );
    }

    /**
     * @param $senderAccountId
     * @param $receiverAccountId
     * @param $phaseId
     * @param $workerType
     */
    private function sendEmail(
        $senderAccountId,
        $receiverAccountId,
        $phaseId,
        $workerType
    ) {
        $this->log->addInfo(
            'Try to send email'
        );
        // get sender data
        $senderData = $this->getAccountDetails($senderAccountId);
        $senderFirstName = $senderData['firstName'];
        $senderLastName = $senderData['lastName'];
        $senderEmail = $senderData['email'];

        // get receiver data
        $receiverData = $this->getAccountDetails($receiverAccountId);
        $receiverFirstName = $receiverData['firstName'];
        $receiverLastName = $receiverData['lastName'];
        $receiverEmail = $receiverData['email'];

        // get phase data
        $phaseData = $this->getPhaseDetails($phaseId);
        $phaseName = $phaseData['name'];

        $from = new Email(
            "$senderFirstName $senderLastName",
            $senderEmail
        );
        $to = new Email(
            "$receiverFirstName $receiverLastName",
            $receiverEmail
        );

        $emailMsg = "Dear $receiverLastName-san,\n\n";
        $subject  = "Notification of a phase ";
        switch ($workerType)
        {
            case self::SUBMISSION_WORKER:
                $emailMsg .=  "A phase ($phaseName) has been"
                    . " submitted by your mentee\n";
                $subject .= "submission";
                break;
            case self::EVALUATION_WORKER:
                 $emailMsg .= "A phase ($phaseName) has been"
                     ." evaluated by your mentor\n";
                $subject .= "evaluation";
                break;
        }
        $emailMsg .= "Please check the link below which"
            ." will take you to our platform\n"
            . self::EMAIL_URL."\n\n "
            ."Best regards,\n"
            ."Engineering Team";

        $content = new Content("text/plain", $emailMsg);
        $mail = new Mail($from, $subject, $to, $content);

        try {
            $response = $this->sendGridConn->client
                ->mail()
                ->send()
                ->post($mail);
            $this->handleSendGridResponse($response);
        } catch(Exception $e) {
            $this->printException($e);
        } catch(Error $e) {
            $this->printError($e);
        }
    }

    /**
     * Get the details of a phase
     *
     * @param  int $phaseId : integer specifying the id of a phase
     * @return array : contains the details of a phase
     */
    private function getPhaseDetails($phaseId)
    {
        try {
            $this->connectToMySql();
            // sender
            $sql = "select name ";
            $sql .= "from $this->dbName.phase ";
            $sql .= "where id=:id";
            $stmt = $this->mysqlConn->prepare($sql);
            $stmt->bindParam(':id', $phaseId);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();

            $name = $result['name'];
            $response = array(
                'name' => $name,
            );
            return $response;
        } catch(Exception $e) {
            $this->printException($e);
        }
    }

    /**
     * Get the details of an account
     *
     * @param  int $accountId : the id of an account
     * @return array : array containing the details of an account
     */
    private function getAccountDetails($accountId)
    {
        try {
            $this->connectToMySql();
            // sender
            $sql  = "select first_name, last_name, email ";
            $sql .= "from $this->dbName.account ";
            $sql .= "where id=:id";
            $stmt = $this->mysqlConn->prepare($sql);
            $stmt->bindParam(':id', $accountId);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();

            $firstName = $result['first_name'];
            $lastName = $result['last_name'];
            $email = $result['email'];

            $response = array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
            );

            return $response;
        } catch(Exception $e) {
            $this->printException($e);
        }
    }

    /**
     * Function to treat send grid error code
     * @param Response $response
     */
    private function handleSendGridResponse($response)
    {
        switch ($response->statusCode()) {
            case 200:
                $this->log->addInfo(
                    'Your message is not queued to be delivered'
                );
                break;
            case 202:
                $this->log->addInfo(
                    'Success in sending email. '
                    .'Your message is queued to be delivered'
                );
                break;
            default:
                $this->log->addError(
                    'error in sending email with sendgrid: '
                    .  json_encode($response->headers())
                );
        }
    }

    /**
     * Print exception into logfile
     * @param Exception $e
     */
    private function printException($e)
    {
        $this->log->addError(
            'exception occurred with detail: '
            .  $e->getMessage()
        );
    }

    /**
     * Print error into logfile
     * @param Error $e
     */
    private function printError($e)
    {
        $this->log->addError(
            'error occurred with detail: '
            .  $e->getMessage()
        );
    }

    /**
     * Connect to RabbitMQ server in the cloud
     */
    private function connectToRabbitMq()
    {
        $this->log->addInfo(
            'Connecting to RabbitMQ server in the cloud'
        );
        $paramsRabbitMq = parse_url(
            getenv(self::MSG_QUEUING_URL_ENV_VAR)
        );
        // connect to RabbitMQ
        $this->amqpConn = new AMQPStreamConnection(
            $paramsRabbitMq['host'],
            5672,
            $paramsRabbitMq['user'],
            $paramsRabbitMq['pass'],
            substr($paramsRabbitMq['path'], 1)
        );
    }

    /**
     * Connect to Send Grid mail server in the cloud
     */
    private function connectToSendGrid()
    {
        $this->log->addInfo(
            'Connecting to SendGrid server in the cloud'
        );
        // set send grid connection
        $sendGridApiKey = getenv(
            self::SENDGRID_API_KEY_ENV_VAR
        );
        $this->sendGridConn = new SendGrid($sendGridApiKey);
    }

    /**
     * Connect to MySQL server in the cloud
     */
    private function connectToMySql()
    {
        $this->log->addInfo(
            'Connecting to MySQL server in the cloud'
        );
        $paramsMysql = parse_url(
            getenv(self::DATABASE_URL_ENV_VAR)
        );
        // connect to MySql
        $dbName = substr($paramsMysql['path'], 1);
        $dsn = "mysql:host=${paramsMysql['host']};dbname=$dbName";
        $this->dbName = $dbName;

        $this->log->addInfo(
            'Creating a new MySQL connection to prevent timeout'
        );
        $this->mysqlConn = new PDO(
            $dsn,
            $paramsMysql['user'],
            $paramsMysql['pass']
        );
        // set the PDO error mode to exception
        $this->mysqlConn->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }
}



