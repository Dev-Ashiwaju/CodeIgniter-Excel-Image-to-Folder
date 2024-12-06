<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class Welcome extends CI_Controller {

    public function index() {
        if ($_FILES['excel_file']['name']) {
            $this->load->library('upload');

            // Configure file upload
            $config['upload_path'] = './uploads/';
            $config['allowed_types'] = 'xlsx|xls';

            // Initialize upload library with the config settings
            $this->upload->initialize($config);

            // Perform file upload
            if (!$this->upload->do_upload('excel_file')) {
                // If upload fails, show the error
                $error = ['error' => $this->upload->display_errors()];
                $this->load->view('welcome_message', $error);
            } else {
                // On successful upload
                $fileData = $this->upload->data();

                // Load the uploaded Excel file using PHPSpreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileData['full_path']);
                $worksheet = $spreadsheet->getActiveSheet();
                $worksheetArray = $worksheet->toArray();
                array_shift($worksheetArray); // Remove the header row

                // Initialize data array to store for database insertion
                $data = [];

                // Loop through the rows in the Excel sheet
                foreach ($worksheetArray as $key => $value) {
                    $drawing = $worksheet->getDrawingCollection()[$key]; // Get the image

                    // Read image contents
                    $zipReader = fopen($drawing->getPath(), 'r');
                    $imageContents = '';
                    while (!feof($zipReader)) {
                        $imageContents .= fread($zipReader, 1024);
                    }
                    fclose($zipReader);

                    // Get the file extension
                    $extension = $drawing->getExtension();

                    // Generate a unique filename
                    $imageFilename = uniqid() . '.' . $extension;

                    // Save the image to 'uploads/images/' directory
                    file_put_contents('./uploads/images/' . $imageFilename, $imageContents);

                    // Image path to store in the database
                    $imagePath = './uploads/images/' . $imageFilename;

                    // Add the extracted data to the $data array for database insertion
                    $data[] = [
                        'sno' => $value[0],
                        'name' => $value[1],
                        'image' => $imagePath // Store image path in the database
                    ];
                }

                // Now, insert the $data array into the database
                $this->load->model('Excel_model');
                $this->Excel_model->insert_data($data);

                // Load success view or any other page
                $this->load->view('upload_success', ['data' => $data]);
            }
        } else {
            // Load the default form page if no file has been submitted
            $this->load->view('welcome_message');
        }
    }

    // Function to extract and save images
    private function _extractImage($sheet, $rowIndex, $columnIndex) {
        $imageFilename = '';

        // Loop through the drawings in the sheet to find images
        foreach ($sheet->getDrawingCollection() as $drawing) {
            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                // Check if the image corresponds to the cell we are processing
                if ($drawing->getCoordinates() === $sheet->getCell('C' . $rowIndex)->getCoordinate()) {
                    // Get the image resource and save it
                    ob_start();
                    switch ($drawing->getMimeType()) {
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG:
                            imagepng($drawing->getImageResource());
                            $extension = 'png';
                            break;
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG:
                            imagejpeg($drawing->getImageResource());
                            $extension = 'jpg';
                            break;
                        default:
                            $extension = 'jpg';
                            break;
                    }
                    $imageData = ob_get_contents();
                    ob_end_clean();

                    // Generate unique filename and save image to 'uploads/images/'
                    $imageFilename = uniqid() . '.' . $extension;
                    file_put_contents('./uploads/' . $imageFilename, $imageData);

                    return './uploads/' . $imageFilename; // Return saved image path
                }
            }
        }

        return null; // Return null if no image is found
    }
}
