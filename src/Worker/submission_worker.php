<?php

require_once 'base_worker.php';

$queue      = 'queue_to_mentor';

$worker = new BaseWorker(
    $queue,
    BaseWorker::SUBMISSION_WORKER
);
$worker->start();





