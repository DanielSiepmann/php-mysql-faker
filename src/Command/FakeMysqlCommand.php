<?php
namespace DSiepmann\Command;

use Doctrine\DBAL;
use Faker\Factory as Faker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FakeMysqlCommand extends Command
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Faker\Generator
     */
    protected $faker;

    /**
     * @var Dbal\Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $userfunctions = [];

    protected function configure()
    {
        $this
            ->setName('fake:mysql')
            ->setDescription('Fake MySQL Data.')

            ->addOption('number', null, InputOption::VALUE_REQUIRED, 'How many fake data should be generated.', 200)

            ->addArgument('config', InputArgument::REQUIRED, 'The configuration file to use.')
            ->addArgument('tableName', InputArgument::REQUIRED, 'The name of the Mysql Table.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->config = Yaml::parse(file_get_contents($input->getArgument('config')));
        $this->faker = Faker::create();
        $this->connection = DBAL\DriverManager::getConnection($this->config['database'], new DBAL\Configuration());

        $this->truncateTable($input->getArgument('tableName'));
        $output->writeLn('<info>Table ' . $input->getArgument('tableName') . ' was truncated.</info>');

        foreach (range(1, $input->getOption('number')) as $number) {
            $this->connection->insert(
                $input->getArgument('tableName'),
                $this->getData($input->getArgument('tableName'))
            );

            $output->writeLn('<info>Inserting record number: ' . $number . '.</info>');
            // if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            //     $output->writeLn('<info>With data: ' . var_export($data, true) . '</info>');
            // }
        }
    }

    protected function truncateTable($tableName)
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeUpdate($platform->getTruncateTableSQL($tableName, false));
    }

    protected function getData($tableName)
    {
        $data = [];

        foreach ($this->connection->getSchemaManager()->listTableColumns($tableName) as $name => $column) {
            $columnData = $this->getColumnData($name, $column);
            if ($columnData !== null) {
                $data[$name] = $columnData;
            }
        }

        return $data;
    }

    protected function getColumnData($name, DBAL\Schema\Column $column)
    {
        if ($column->getAutoincrement()) {
            return;
        }

        if (isset($this->config['data'][$name])) {
            return $this->getConfiguredColumnData($this->config['data'][$name]);
        }

        if (is_callable([$this->faker, $column->getType()])) {
            return call_user_func([$this->faker, $column->getType()]);
        }

        return '';
    }

    protected function getConfiguredColumnData($config)
    {
        if ($config['type'] === 'static') {
            return $config['value'];
        }

        if ($config['type'] === 'userfunc') {
            if (! isset($this->userfunc[$config['class']])) {
                $this->userfunc[$config['class']] = new $config['class'];
            }

            $userfunction = $this->userfunc[$config['class']];
            $userMethod = $config['method'];

            return $userfunction->$userMethod();
        }

        if ($config['type'] === 'faker') {
            $formatterToUse = $config['formatter'];

            $fakerData = $this->faker->$formatterToUse();

            if ($fakerData instanceof \DateTime) {
                $fakerData = $fakerData->format('Y-m-d H:i:s');
            }

            return $fakerData;
        }

        throw new \Exception('Unkown configured value.', 1486135949);
    }
}
