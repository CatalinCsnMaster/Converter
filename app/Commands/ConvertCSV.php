<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use OzdemirBurak\JsonCsv\File\Csv;
use OzdemirBurak\JsonCsv\File\Json;

class ConvertCSV extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'convert';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Convert files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $initialFormat = $this->choichesMenu('Which type of file to do you want to convert?');
        $finalFormat = $this->choichesMenu("In what format do you want to convert the $initialFormat file?");
        $path = $this->inputMenu("Please provide the path to the file");

        $this->convertingFile($path, $initialFormat, $finalFormat);
    }

    private function choichesMenu(string $menuTitle)
    {
        return $this->menu($menuTitle)
                    ->addOption('csv', 'CSV')
                    ->addOption('json', 'JSON')
                    ->addOption('xml', 'XML')
                    ->setForegroundColour('green')
                    ->setBackgroundColour('black')
                    ->setWidth(1200)
                    ->setBorder(2, 'white')
                    ->open();
    }

    private function inputMenu(string $menuTitle)
    {
        return $this->menu($menuTitle)
                    ->addQuestion($menuTitle, 'Enter Path')
                    ->setForegroundColour('green')
                    ->setBackgroundColour('black')
                    ->setWidth(1200)
                    ->setBorder(2, 'white')
                    ->open();
    }

    private function convertingFile(string $path, string $initialFormat, string $finalFormat)
    {
        $functionName = $initialFormat.'To'.$finalFormat;
        $foramtted = $this->$functionName($path);
        $this->saveFormattedFile($foramtted, $finalFormat, basename($path, $initialFormat));
    }

    private function csvToJson(string $fname)
    {
        $csv = new Csv($fname);
        $csv->setConversionKey('options', JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return $csv->convert();
    }

    private function jsonToCsv(string $fname)
    {
        $json = new Json($fname);
        return $json->convert();
    }

    private function csvToXml(string $fname)
    {
        // Open csv file for reading
        $inputFile  = fopen($fname, 'rt');

        // Get the headers of the file
        $headers = fgetcsv($inputFile);

        // Create a new dom document with pretty formatting
        $doc  = new \DomDocument();
        $doc->formatOutput   = true;

        // Add a root node to the document
        $root = $doc->createElement('policies');
        $root = $doc->appendChild($root);

        // Loop through each row creating a <policy> node with the correct data
        while (($row = fgetcsv($inputFile)) !== FALSE)
        {
            $container = $doc->createElement('policy');

            foreach($headers as $i => $header)
            {
                $child = $doc->createElement($header);
                $child = $container->appendChild($child);
                $value = $doc->createTextNode($row[$i]);
                $value = $child->appendChild($value);
            }
            $root->appendChild($container);
        }

        return $doc->saveXML();
    }

    private function saveFormattedFile($contentOfFile, string $fileType, string $fileName)
    {
        return file_put_contents("$fileName.$fileType", $contentOfFile);
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
