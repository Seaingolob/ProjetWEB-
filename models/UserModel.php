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
            // ÉTAPE 1: On récupère l'utilisateur
            // J'ai renommé la variable $stmt en $stmt_user pour plus de clarté
            $stmt_user = $this->connexion->prepare("SELECT id_compte, nom, prenom, mail, telephone 
                             FROM utilisateur 
                             WHERE id_compte = :id");
            $stmt_user->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt_user->execute();
            // J'utilise directement $userData comme variable principale
            $userData = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            // Si l'utilisateur n'existe pas, on retourne null tout de suite
            if (!$userData) {
                return [
                    'user' => null,
                    'user_type' => null,
                    'specific_info' => []
                ];
            }
            
            // SUPPRESSION de la copie $userData = $user qui créait la confusion
            // On utilise directement $userData comme la variable principale
            // ÉTAPE 2: On récupère le type d'utilisateur
            $user_type = $_SESSION['user_type'];
            
            // ÉTAPE 3: On récupère les infos spécifiques
            $specific_info = [];
        
            if ($user_type === 'etudiant') {
                // Récupérer la wishlist de l'étudiant avec statut de postulation
                // Autre variable $stmt pour cette requête
                $stmt_wishlist = $this->connexion->prepare("SELECT 
                                                o.id_offre, 
                                                o.titre, 
                                                e.nom as entreprise_nom, 
                                                GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as competences, 
                                                v.nom_ville,
                                                CASE 
                                                    WHEN EXISTS (SELECT 1 FROM postuler p WHERE p.id_compte = :id AND p.id_offre = o.id_offre) THEN 'Postulée'
                                                    ELSE 'Non-postulée'
                                                END AS statut_postulation
                                            FROM souhaiter s 
                                            JOIN offre o ON s.id_offre = o.id_offre 
                                            JOIN entreprise e ON o.id_entreprise = e.id_entreprise 
                                            JOIN adresse a ON e.id_adresse = a.id_adresse 
                                            JOIN ville v ON a.id_ville = v.id_ville 
                                            LEFT JOIN contenir co ON o.id_offre = co.id_offre 
                                            LEFT JOIN competence c ON co.id_competence = c.id_competence 
                                            WHERE s.id_compte = :id 
                                            GROUP BY o.id_offre");
                $stmt_wishlist->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt_wishlist->execute();
                $specific_info['wishlist'] = $stmt_wishlist->fetchAll(PDO::FETCH_ASSOC);
    
                // Récupérer les informations sur la promotion de l'étudiant
                // Autre variable $stmt pour cette requête
                $stmt_promo = $this->connexion->prepare("SELECT 
                    p.nom as promotion_nom,
                    p.id_promotion,
                    c.nom_campus, 
                    a.debut, 
                    a.fin
                    FROM appartenir a
                    JOIN promotion p ON a.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE a.id_compte = :id
                    
                    ORDER BY a.debut DESC
                    LIMIT 1");
                $stmt_promo->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt_promo->execute();
                $specific_info['promotion'] = $stmt_promo->fetch(PDO::FETCH_ASSOC);
            } elseif ($user_type === 'pilote') {
                // Récupérer les promotions pilotées
                // Autre variable $stmt pour cette requête
                $stmt_pilote = $this->connexion->prepare("SELECT 
                    p.id_promotion,
                    p.nom as promotion_nom, 
                    c.nom_campus,
                    pi.debut,
                    pi.fin
                    FROM piloter pi
                    JOIN promotion p ON pi.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE pi.id_compte = :id
                    AND (pi.debut <= CURRENT_DATE() AND (pi.fin >= CURRENT_DATE() OR pi.fin IS NULL))
                    ORDER BY pi.debut DESC");
                $stmt_pilote->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt_pilote->execute();
                $specific_info['promotions_pilotees'] = $stmt_pilote->fetchAll(PDO::FETCH_ASSOC);
            }
    
            // On s'assure d'utiliser $userData pour retourner les infos de l'utilisateur
            return [
                'user' => $userData,  // C'est ici qu'on utilise $userData, pas $user
                'user_type' => $user_type,
                'specific_info' => $specific_info
            ];
            
        } catch (PDOException $e) {
            error_log("ERREUR CRITIQUE dans getUserInfo(): " . $e->getMessage());
            return [
                'user' => "erreur",
                'user_type' => "erreur",
                'specific_info' => [ ]
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