<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once CONTROLLER_PATH . '/appointmentsController.php';

$controller = new appointmentsController();
$controller->updateAppointment();
