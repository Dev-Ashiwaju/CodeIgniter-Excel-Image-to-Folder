<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel_model extends CI_Model {

    public function insert_data($data) {
        // Insert data into your_table
        foreach ($data as $row) {
            $this->db->insert('biodata', [
                'sno' => $row['sno'],
                'name' => $row['name'],
                'image' => $row['image'], // Store image path
            ]);
        }
    }
}

?>