<?php

class UserModel {
    private $connexion;

    public function __construct() {
        // Ici, on récupère directement l'instance PDO retournée par config.php
        $this->connexion = require __DIR__ . '/../config/config.php';
    }

    public function getFormOptions() {
        try {
            $campus = $this->connexion->query("SELECT id_campus, nom_campus FROM campus ORDER BY nom_campus")->fetchAll(PDO::FETCH_ASSOC);
            $promotions = $this->connexion->query("SELECT id_promotion, nom FROM promotion ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
            $regions = $this->connexion->query("SELECT id_region, nom_region FROM region ORDER BY nom_region")->fetchAll(PDO::FETCH_ASSOC);
            $villes = $this->connexion->query("SELECT id_ville, nom_ville, id_region FROM ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);
            return compact('campus', 'promotions', 'regions', 'villes');
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addUser($data) {
        try {
            $this->connexion->beginTransaction();
    
            $nom = trim(htmlspecialchars($data['nom']));
            $prenom = trim(htmlspecialchars($data['prenom']));
            $mail = filter_var($data['mail'], FILTER_SANITIZE_EMAIL);
            $mot_de_passe_hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            $telephone = trim(htmlspecialchars($data['telephone']));
            $type_utilisateur = $data['type_utilisateur'];
    
            // Générer l'id_compte : première lettre du prénom + "_" + nom (ex: "J_Dupont")
            $id_compte_base = strtolower(substr($prenom, 0, 1)) . '_' . strtolower($nom);
            $id_compte = $id_compte_base;
    
            // Vérifier l'unicité de l'id_compte, si déjà utilisé, on ajoute un nombre aléatoire
            $stmt = $this->connexion->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_compte = ?");
            $suffix = 1;
            while (true) {
                $stmt->execute([$id_compte]);
                if ($stmt->fetchColumn() == 0) {
                    break;
                }
                $id_compte = $id_compte_base . $suffix;
                $suffix++;
            }
    
            // Insérer dans la table utilisateur
            $stmt = $this->connexion->prepare(
                "INSERT INTO utilisateur (id_compte, nom, prenom, mail, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id_compte, $nom, $prenom, $mail, $mot_de_passe_hash, $telephone]);
    
            // Ensuite, tu utilises $id_compte partout pour la suite
            if ($type_utilisateur === 'admin') {
                $stmt = $this->connexion->prepare("INSERT INTO admin (id_compte) VALUES (?)");
                $stmt->execute([$id_compte]);
            } else {
                // Gestion du campus
                if ($data['campus-choix'] === 'existant') {
                    $id_campus = intval($data['campus-id']);
                } else {
                    $nom_campus = htmlspecialchars($data['nouveau-campus-nom']);
                    $adresse_complete = htmlspecialchars($data['adresse']);
                    $id_ville = null;
    
                    if ($data['ville-choix'] === 'existante') {
                        $id_ville = intval($data['ville_id']);
                    } else {
                        $nouvelle_ville_nom = htmlspecialchars($data['nouvelle_ville_nom']);
                        $id_region = intval($data['region_id']);
                        $stmt = $this->connexion->prepare("INSERT INTO ville (nom_ville, id_region) VALUES (?, ?)");
                        $stmt->execute([$nouvelle_ville_nom, $id_region]);
                        $id_ville = $this->connexion->lastInsertId();
                    }
    
                    $stmt = $this->connexion->prepare("INSERT INTO adresse (nom_adresse, id_ville) VALUES (?, ?)");
                    $stmt->execute([$adresse_complete, $id_ville]);
                    $id_adresse = $this->connexion->lastInsertId();
    
                    $stmt = $this->connexion->prepare("INSERT INTO campus (nom_campus, id_adresse) VALUES (?, ?)");
                    $stmt->execute([$nom_campus, $id_adresse]);
                    $id_campus = $this->connexion->lastInsertId();
                }
    
                // Promotion
                if ($data['promotion-choix'] === 'existante') {
                    $id_promotion = intval($data['promotion-id']);
                } else {
                    $nom_promotion = htmlspecialchars($data['nouvelle-promotion-nom']);
                    $stmt = $this->connexion->prepare("INSERT INTO promotion (nom, id_campus) VALUES (?, ?)");
                    $stmt->execute([$nom_promotion, $id_campus]);
                    $id_promotion = $this->connexion->lastInsertId();
                }
    
                $today = date("Y-m-d");
    
                if ($type_utilisateur === 'etudiant') {
                    $stmt = $this->connexion->prepare("INSERT INTO etudiant (id_compte) VALUES (?)");
                    $stmt->execute([$id_compte]);
                    $stmt = $this->connexion->prepare("INSERT INTO appartenir (id_compte, id_promotion, debut) VALUES (?, ?, ?)");
                    $stmt->execute([$id_compte, $id_promotion, $today]);
                } elseif ($type_utilisateur === 'pilote') {
                    $stmt = $this->connexion->prepare("INSERT INTO pilote (id_compte) VALUES (?)");
                    $stmt->execute([$id_compte]);
                    $stmt = $this->connexion->prepare("INSERT INTO piloter (id_compte, id_promotion, debut) VALUES (?, ?, ?)");
                    $stmt->execute([$id_compte, $id_promotion, $today]);
                }
            }
    
            $this->connexion->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->connexion->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUsers($search, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
    
        // Compter le nombre total d'utilisateurs
        $sql_count = "SELECT COUNT(id_compte) FROM utilisateur WHERE nom LIKE :search_nom OR prenom LIKE :search_prenom";
        $stmt_count = $this->connexion->prepare($sql_count);
        $stmt_count->bindValue(':search_nom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->bindValue(':search_prenom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->execute();
        $totalItems = $stmt_count->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
    
        // Récupérer les utilisateurs
        $sql = "SELECT id_compte, nom, prenom, mail, telephone 
                FROM utilisateur 
                WHERE nom LIKE :search_nom OR prenom LIKE :search_prenom 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(':search_nom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':search_prenom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
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
            // 1. On récupère l'utilisateur (infos de base)
            $stmt = $this->connexion->prepare(
                "SELECT id_compte, nom, prenom, mail, telephone 
                 FROM utilisateur 
                 WHERE id_compte = :id"
            );
            $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                return [
                    'user' => null,
                    'user_type' => null,
                    'specific_info' => []
                ];
            }
    
            $authModel = new AuthModel();
            $user_type = $authModel->get_user_type($id_compte);

            $specific_info = [];
    
            if ($user_type === 'etudiant') {
                // On utilise des alias de paramètres pour la requête complexe
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        o.id_offre, 
                        o.titre, 
                        e.nom AS entreprise_nom, 
                        GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') AS competences, 
                        v.nom_ville,
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM postuler p 
                                WHERE p.id_compte = :id_postulant AND p.id_offre = o.id_offre
                            ) THEN 'Postulée'
                            ELSE 'Non-postulée'
                        END AS statut_postulation
                    FROM souhaiter s
                    JOIN offre o ON s.id_offre = o.id_offre
                    JOIN entreprise e ON o.id_entreprise = e.id_entreprise
                    JOIN adresse a ON e.id_adresse = a.id_adresse
                    JOIN ville v ON a.id_ville = v.id_ville
                    LEFT JOIN contenir co ON o.id_offre = co.id_offre
                    LEFT JOIN competence c ON co.id_competence = c.id_competence
                    WHERE s.id_compte = :id_souhait
                    GROUP BY o.id_offre"
                );
                $stmt->bindParam(':id_postulant', $id_compte, PDO::PARAM_STR);
                $stmt->bindParam(':id_souhait', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['wishlist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                // Promotion de l'étudiant (actuelle)
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        p.nom AS promotion_nom,
                        p.id_promotion,
                        c.nom_campus, 
                        a.debut, 
                        a.fin
                    FROM appartenir a
                    JOIN promotion p ON a.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE a.id_compte = :id
                    AND (a.debut <= CURRENT_DATE() AND (a.fin >= CURRENT_DATE() OR a.fin IS NULL))
                    ORDER BY a.debut DESC
                    LIMIT 1"
                );
                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['promotion'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
            } elseif ($user_type === 'pilote') {
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        p.id_promotion,
                        p.nom AS promotion_nom, 
                        c.nom_campus,
                        pi.debut,
                        pi.fin
                    FROM piloter pi
                    JOIN promotion p ON pi.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE pi.id_compte = :id
                    AND (pi.debut <= CURRENT_DATE() AND (pi.fin >= CURRENT_DATE() OR pi.fin IS NULL))
                    ORDER BY pi.debut DESC"
                );
                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['promotions_pilotees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
    
            return [
                'user' => $user,
                'user_type' => $user_type,
                'specific_info' => $specific_info
            ];
    
        } catch (PDOException $e) {
            error_log("ERREUR CRITIQUE dans getUserInfo(): " . $e->getMessage());
            return [
                'user' => "erreur",
                'user_type' => "erreur",
                'specific_info' => [
                    'error_message' => $e->getMessage()
                ]
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