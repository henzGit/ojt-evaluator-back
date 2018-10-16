<?php

require_once 'base_worker.php';

$queue      = 'queue_to_mentee';

$worker = new BaseWorker(
    $queue,
    BaseWorker::EVALUATION_WORKER
);
$worker->start();





