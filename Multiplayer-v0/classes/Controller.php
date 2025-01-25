<?php 

require_once __DIR__ . '/../../Webler/classes/Controller.php';

class MultiplayerController extends Controller {
    
    public function createApplication($applicationName, $password) {
        // Validate unique application name
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM M_APPS WHERE application_name = :name");
        $checkStmt->execute([':name' => $applicationName]);
        
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("Application name already exists");
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new application
        $stmt = $this->db->prepare("INSERT INTO M_APPS (application_name, password) VALUES (:name, :password)");
        $stmt->execute([
            ':name' => $applicationName,
            ':password' => $hashedPassword
        ]);
        
        return $applicationName;
    }
    
    public function authenticateApplication($applicationName, $password) {
        // Fetch stored password
        $stmt = $this->db->prepare("SELECT password FROM M_APPS WHERE application_name = :name");
        $stmt->execute([':name' => $applicationName]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        return $result ? password_verify($password, $result['password']) : false;
    }
    
    

}

?>