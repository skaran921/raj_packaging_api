<?php
class User_model extends CI_Model
{
    private $table = 'users';

    public function getUserByEmail($email)
    {
        return $this->db->get_where($this->table, [
            'USER_EMAIL' => $email,
            'USER_STATUS' => 1
        ])->row_array();
    }

    public function createUser($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
}
