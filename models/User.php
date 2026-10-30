<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($username, $email, $password, $role = 'student') {
        if (!in_array($role, ['student', 'teacher'])) {
            $role = 'student';
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        try {
            return $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash,
                ':role' => $role
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, role, profile_photo, cover_photo, bio, points, created_at, is_banned FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function updateProfilePhoto($id, $photo_path) {
        $stmt = $this->db->prepare("UPDATE users SET profile_photo = :photo WHERE id = :id");
        return $stmt->execute([':photo' => $photo_path, ':id' => $id]);
    }

    public function updateCoverPhoto($id, $photo_path) {
        $stmt = $this->db->prepare("UPDATE users SET cover_photo = :photo WHERE id = :id");
        return $stmt->execute([':photo' => $photo_path, ':id' => $id]);
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, email, role, created_at, is_banned FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':id' => $id]);
    }

    public function toggleBan($id, $status) {
        $stmt = $this->db->prepare("UPDATE users SET is_banned = :status WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function setResetToken($email, $token, $expiry) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = :token, reset_expires = :expiry WHERE email = :email");
        $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':email' => $email
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getUserByResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW()");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function updatePassword($id, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        return $stmt->execute([
            ':password' => $hash,
            ':id' => $id
        ]);
    }

    public function updateBio($id, $bio) {
        $stmt = $this->db->prepare("UPDATE users SET bio = :bio WHERE id = :id");
        return $stmt->execute([
            ':bio' => $bio,
            ':id'  => $id
        ]);
    }

    public function updateRole($id, $role) {
        if (!in_array($role, ['student', 'teacher'])) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id AND role != 'admin'");
        return $stmt->execute([
            ':role' => $role,
            ':id'   => $id
        ]);
    }

    public function addPoints($id, $amount) {
        $stmt = $this->db->prepare("UPDATE users SET points = points + :amount WHERE id = :id");
        return $stmt->execute([':amount' => $amount, ':id' => $id]);
    }

    public function getTopUsers($limit = 10) {
        $stmt = $this->db->prepare("SELECT id, username, role, profile_photo, points FROM users WHERE is_banned = 0 AND role != 'admin' ORDER BY points DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
