<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\EntityManagerInterface;

/** @var EntityManagerInterface $entityManager */
$entityManager = require_once __DIR__ . '/config/bootstrap.php';

return ConsoleRunner::createHelperSet($entityManager);
