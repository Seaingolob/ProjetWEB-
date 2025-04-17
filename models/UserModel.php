<?php
// models/UserModel.php
class UserModel {
    private $connexion;
    
    public function __construct() {
        // Inclure config et se connecter à la BD
        require_once __DIR__ . '/../config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function getUsers($search, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        
        // Compter le nombre total d'utilisateurs
        $sql_count = "SELECT COUNT(id_compte) FROM utilisateur WHERE nom LIKE :search OR prenom LIKE :search";
        $stmt_count = $this->connexion->prepare($sql_count);
        $stmt_count->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->execute();
        $totalItems = $stmt_count->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Récupérer les utilisateurs
        $sql = "SELECT id_compte, nom, prenom, mail, telephone 
                FROM utilisateur 
                WHERE nom LIKE :search OR prenom LIKE :search 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'users' => $utilisateurs,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }
    
    public function getUserInfo($id_compte) {
        try {
            // IMPORTANT: On vérifie l'utilisateur correctement
            echo "ID de l'utilisateur : " . $id_compte . "<br>";  // Débogage temporaire
            $verify = $this->connexion->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_compte = :id");
            $verify->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $verify->execute();
            $count = (int)$verify->fetchColumn();  // CRUCIAL: on convertit explicitement en int
            
            echo "Nombre d'utilisateurs trouvés : " . $count . "<br>";  // Débogage temporaire
            
            if ($count === 0) {  // On compare avec === pour être sûr
                return [
                    'user' => null,
                    'user_type' => null,
                    'specific_info' => []
                ];
            }
            
            // Le reste est exactement comme avant
            $stmt = $this->connexion->prepare("SELECT id_compte, nom, prenom, mail, telephone 
                                 FROM utilisateur 
                                 WHERE id_compte = :id");
            $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Détermination du type d'utilisateur
            $stmt = $this->connexion->prepare("SELECT 
                                CASE 
                                    WHEN EXISTS (SELECT 1 FROM etudiant WHERE id_compte = :id) THEN 'etudiant'
                                    WHEN EXISTS (SELECT 1 FROM admin WHERE id_compte = :id) THEN 'admin'
                                    WHEN EXISTS (SELECT 1 FROM pilote WHERE id_compte = :id) THEN 'pilote'
                                    ELSE 'inconnu'
                                END AS user_type");
            $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt->execute();
            $user_type_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_type = $user_type_result['user_type'];
            
            // La suite de ton code...
            // ... (tout le reste est inchangé)
            
            return [
                'user' => $user,
                'user_type' => $user_type,
                'specific_info' => $specific_info
            ];
            
        } catch (PDOException $e) {
            error_log("ERREUR CRITIQUE dans getUserInfo(): " . $e->getMessage());
            return [
                'user' => null,
                'user_type' => null,
                'specific_info' => []
            ];
        }
    }
    
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM utilisateur WHERE id_compte = :id";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }
}