<?php
class DbConnectionUserDecorator {
    public function __set($key, $value) {
        $this->conn->$key = $value;
    }
}
