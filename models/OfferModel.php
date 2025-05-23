<?php
// models/OfferModel.php
class OfferModel {
    private $connexion;
    
    public function __construct() {
        // Inclure config et se connecter à la BD
        $this->connexion = require __DIR__ . '/../config/config.php';
    }

    public function getFormOptions() {
        try {
            $sqls = [
                'competences' => "SELECT id_competence, nom FROM competence ORDER BY nom",
                'entreprises' => "SELECT id_entreprise, nom FROM entreprise ORDER BY nom",
                'villes' => "SELECT id_ville, nom_ville FROM ville ORDER BY nom_ville",
                'secteurs' => "SELECT id_secteur_activite, nom FROM secteur_activite ORDER BY nom",
                'regions' => "SELECT id_region, nom_region FROM region ORDER BY nom_region"
            ];
            $result = [];
            foreach ($sqls as $key => $query) {
                $stmt = $this->connexion->prepare($query);
                $stmt->execute();
                $result[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addOffer($data, $id_compte) {
        try {
            // Récupération des données du formulaire pour l'offre
            $titre = htmlspecialchars($data['titre']);
            $duree = intval($data['duree']);
            $date_publication = $data['date_publication'];
            $description = htmlspecialchars($data['description']);
            $id_entreprise = null;

            // Gestion entreprise
            if ($data['entreprise-choix'] === 'existante') {
                $id_entreprise = intval($data['entreprise-id']);
            } else {
                // Nouvelle entreprise, gestion cascade
                $nom_entreprise = htmlspecialchars($data['nouvelle-entreprise-nom']);
                $entreprise_description = htmlspecialchars($data['entreprise-description']);
                $entreprise_site = filter_var($data['entreprise-site'], FILTER_SANITIZE_URL);

                // Région
                if ($data['region-choix'] === 'existante') {
                    $id_region = intval($data['region']);
                } else {
                    $nom_region = htmlspecialchars($data['nouvelle-region-nom']);
                    $stmt = $this->connexion->prepare("INSERT INTO region (nom_region) VALUES (?)");
                    $stmt->execute([$nom_region]);
                    $id_region = $this->connexion->lastInsertId();
                }

                // Ville
                if ($data['ville-choix'] === 'existante') {
                    $id_ville = intval($data['ville']);
                } else {
                    $nom_ville = htmlspecialchars($data['nouvelle-ville-nom']);
                    $stmt = $this->connexion->prepare("INSERT INTO ville (nom_ville, id_region) VALUES (?, ?)");
                    $stmt->execute([$nom_ville, $id_region]);
                    $id_ville = $this->connexion->lastInsertId();
                }

                // Adresse
                $adresse = htmlspecialchars($data['adresse']);
                $stmt = $this->connexion->prepare("INSERT INTO adresse (nom_adresse, id_ville) VALUES (?, ?)");
                $stmt->execute([$adresse, $id_ville]);
                $id_adresse = $this->connexion->lastInsertId();

                // Entreprise
                $stmt = $this->connexion->prepare("INSERT INTO entreprise (nom, description, site, id_adresse) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom_entreprise, $entreprise_description, $entreprise_site, $id_adresse]);
                $id_entreprise = $this->connexion->lastInsertId();

                // Secteurs existants
                if (isset($data['secteurs']) && is_array($data['secteurs'])) {
                    foreach ($data['secteurs'] as $id_secteur) {
                        $stmt = $this->connexion->prepare("INSERT INTO travailler (id_entreprise, id_secteur_activite) VALUES (?, ?)");
                        $stmt->execute([$id_entreprise, $id_secteur]);
                    }
                }
                // Nouveaux secteurs
                if (isset($data['nouveau-secteur-check']) && isset($data['nouveaux-secteurs']) && !empty($data['nouveaux-secteurs'])) {
                    $nouveaux_secteurs = explode(',', $data['nouveaux-secteurs']);
                    foreach ($nouveaux_secteurs as $nouveau_secteur) {
                        $nouveau_secteur = trim($nouveau_secteur);
                        if (!empty($nouveau_secteur)) {
                            $stmt = $this->connexion->prepare("SELECT id_secteur_activite FROM secteur_activite WHERE nom = ?");
                            $stmt->execute([$nouveau_secteur]);
                            $secteur = $stmt->fetch(PDO::FETCH_ASSOC);
                            if (!$secteur) {
                                $stmt = $this->connexion->prepare("INSERT INTO secteur_activite (nom) VALUES (?)");
                                $stmt->execute([$nouveau_secteur]);
                                $id_secteur = $this->connexion->lastInsertId();
                            } else {
                                $id_secteur = $secteur['id_secteur_activite'];
                            }
                            $stmt = $this->connexion->prepare("INSERT INTO travailler (id_entreprise, id_secteur_activite) VALUES (?, ?)");
                            $stmt->execute([$id_entreprise, $id_secteur]);
                        }
                    }
                }
            }

            // Ajout de l'offre
            $stmt = $this->connexion->prepare("INSERT INTO offre (titre, description, duree_mois, date_publication, id_entreprise, id_compte) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $duree, $date_publication, $id_entreprise, $id_compte]);
            $id_offre = $this->connexion->lastInsertId();

            // Compétences existantes
            if (isset($data['competences']) && is_array($data['competences'])) {
                foreach ($data['competences'] as $id_competence) {
                    $stmt = $this->connexion->prepare("INSERT INTO contenir (id_offre, id_competence) VALUES (?, ?)");
                    $stmt->execute([$id_offre, $id_competence]);
                }
            }
            // Nouvelles compétences
            if (isset($data['nouvelle-competence-check']) && isset($data['nouvelles-competences']) && !empty($data['nouvelles-competences'])) {
                $nouvelles_competences = explode(',', $data['nouvelles-competences']);
                foreach ($nouvelles_competences as $nouvelle_competence) {
                    $nouvelle_competence = trim($nouvelle_competence);
                    if (!empty($nouvelle_competence)) {
                        $stmt = $this->connexion->prepare("SELECT id_competence FROM competence WHERE nom = ?");
                        $stmt->execute([$nouvelle_competence]);
                        $comp = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$comp) {
                            $stmt = $this->connexion->prepare("INSERT INTO competence (nom) VALUES (?)");
                            $stmt->execute([$nouvelle_competence]);
                            $id_competence = $this->connexion->lastInsertId();
                        } else {
                            $id_competence = $comp['id_competence'];
                        }
                        $stmt = $this->connexion->prepare("INSERT INTO contenir (id_offre, id_competence) VALUES (?, ?)");
                        $stmt->execute([$id_offre, $id_competence]);
                    }
                }
            }

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getOffers($search, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        
        // Compter le nombre total d'offres
        $sql_count = "SELECT COUNT(id_offre) FROM offre WHERE titre LIKE :search";
        $stmt_count = $this->connexion->prepare($sql_count);
        $stmt_count->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->execute();
        $totalItems = $stmt_count->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Récupérer les offres
        $sql = "SELECT id_offre, titre, description, duree_mois, date_publication 
                FROM offre 
                WHERE titre LIKE :search 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'offers' => $offres,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }

    public function getPDO() {
        return $this->connexion;
    }

    public function getFeaturedOffers() {
        $sql = "SELECT o.id_offre, o.titre, o.duree_mois, o.date_publication, o.id_entreprise, 
                ev.nom AS nom_entreprise, v.nom_ville, AVG(e.note) AS moyenne_note
                FROM offre o
                LEFT JOIN evaluation e ON o.id_offre = e.id_offre
                JOIN entreprise ev ON o.id_entreprise = ev.id_entreprise
                JOIN adresse ad ON ev.id_adresse = ad.id_adresse
                JOIN ville v ON ad.id_ville = v.id_ville
                LEFT JOIN contenir co ON o.id_offre = co.id_offre
                LEFT JOIN competence c ON co.id_competence = c.id_competence
                GROUP BY o.id_offre, ev.nom, v.nom_ville
                ORDER BY moyenne_note DESC
                LIMIT 2";
        
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute();
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les compétences pour chaque offre
        foreach ($offres as &$offre) {
            $offre['competences'] = $this->getCompetencesForOffer($offre['id_offre']);
        }
        
        return $offres;
    }
    
    public function getCompetencesForOffer($id_offre) {
        $sql = "SELECT c.nom 
                FROM competence c
                INNER JOIN contenir co ON c.id_competence = co.id_competence
                WHERE co.id_offre = :id_offre";
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute([':id_offre' => $id_offre]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getUserLikedOffers($userId) {
        $sql = "SELECT id_offre FROM souhaiter WHERE id_compte = :user_id";
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $likedOffers = [];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $row) {
            $likedOffers[] = $row['id_offre'];
        }
        
        return $likedOffers;
    }
    
    public function toggleLike($userId, $offerId) {
        // Vérifier si l'utilisateur a déjà liké cette offre
        $sqlCheck = "SELECT * FROM souhaiter WHERE id_compte = :user_id AND id_offre = :offer_id";
        $stmtCheck = $this->connexion->prepare($sqlCheck);
        $stmtCheck->execute([':user_id' => $userId, ':offer_id' => $offerId]);
        
        if ($stmtCheck->rowCount() > 0) {
            // L'utilisateur a déjà liké cette offre, on supprime le like
            $sqlDelete = "DELETE FROM souhaiter WHERE id_compte = :user_id AND id_offre = :offer_id";
            $stmtDelete = $this->connexion->prepare($sqlDelete);
            $stmtDelete->execute([':user_id' => $userId, ':offer_id' => $offerId]);
            return false; // Retourne false pour indiquer que l'offre n'est plus likée
        } else {
            // L'utilisateur n'a pas encore liké cette offre, on ajoute le like
            $sqlInsert = "INSERT INTO souhaiter (id_compte, id_offre) VALUES (:user_id, :offer_id)";
            $stmtInsert = $this->connexion->prepare($sqlInsert);
            $stmtInsert->execute([':user_id' => $userId, ':offer_id' => $offerId]);
            return true; // Retourne true pour indiquer que l'offre est maintenant likée
        }
    }

    public function getWishlistOffers($userId) {
        $sql = "SELECT o.*, e.nom AS nom_entreprise, v.nom_ville 
                FROM offre o 
                JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
                JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
                JOIN ville v ON ad.Id_ville = v.Id_ville
                JOIN souhaiter s ON o.id_offre = s.id_offre
                WHERE s.id_compte = :user_id
                ORDER BY o.date_publication DESC";
    
        try {
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
            $stmt->execute();
            $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les compétences pour chaque offre
            foreach ($offres as &$offre) {
                $offre['competences'] = $this->getCompetencesForOffer($offre['id_offre']);
            }
            
            return $offres;
        } catch (PDOException $e) {
            // Gestion de l'erreur
            error_log("Erreur lors de la récupération des offres de la wishlist: " . $e->getMessage());
            return [];
        }
    }

    public function getAllCompanies() {
        $sql = "SELECT DISTINCT nom FROM entreprise ORDER BY nom";
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getAllCities() {
        $sql = "SELECT DISTINCT nom_ville FROM ville ORDER BY nom_ville";
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getAllCompetences() {
        $sql = "SELECT DISTINCT nom FROM competence ORDER BY nom";
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getOfferDetails($id_offre, $user_id) {
        // Récupère tous les détails nécessaires d'une offre et ses relations

        // 1. Détail de l'offre
        $stmt = $this->connexion->prepare(
            "SELECT o.*, e.nom AS entreprise_nom, e.description AS entreprise_description, e.site AS entreprise_site,
                    a.nom_adresse, v.nom_ville, r.nom_region, u.nom AS createur_nom, u.prenom AS createur_prenom, u.id_compte AS createur_id
             FROM offre o
             JOIN entreprise e ON o.id_entreprise = e.id_entreprise
             JOIN adresse a ON e.id_adresse = a.id_adresse
             JOIN ville v ON a.id_ville = v.id_ville
             JOIN region r ON v.id_region = r.id_region
             JOIN utilisateur u ON o.id_compte = u.id_compte
             WHERE o.id_offre = :id");
        $stmt->bindParam(':id', $id_offre);
        $stmt->execute();
        $offre = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$offre) return false;

        // 2. Compétences
        $stmt = $this->connexion->prepare(
            "SELECT c.id_competence, c.nom
             FROM contenir co
             JOIN competence c ON co.id_competence = c.id_competence
             WHERE co.id_offre = :id");
        $stmt->bindParam(':id', $id_offre);
        $stmt->execute();
        $competences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Secteurs
        $stmt = $this->connexion->prepare(
            "SELECT sa.id_secteur_activite, sa.nom
             FROM travailler t
             JOIN secteur_activite sa ON t.id_secteur_activite = sa.id_secteur_activite
             JOIN entreprise e ON t.id_entreprise = e.id_entreprise
             JOIN offre o ON o.id_entreprise = e.id_entreprise
             WHERE o.id_offre = :id");
        $stmt->bindParam(':id', $id_offre);
        $stmt->execute();
        $secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Postulation ?
        $stmt = $this->connexion->prepare(
            "SELECT COUNT(*) AS postule
             FROM postuler WHERE id_compte = :id_compte AND id_offre = :id_offre");
        $stmt->bindParam(':id_compte', $user_id);
        $stmt->bindParam(':id_offre', $id_offre);
        $stmt->execute();
        $postule = $stmt->fetch(PDO::FETCH_ASSOC)['postule'] > 0;

        // 5. Wishlist ?
        $stmt = $this->connexion->prepare(
            "SELECT COUNT(*) AS wishlist
             FROM souhaiter WHERE id_compte = :id_compte AND id_offre = :id_offre");
        $stmt->bindParam(':id_compte', $user_id);
        $stmt->bindParam(':id_offre', $id_offre);
        $stmt->execute();
        $wishlist = $stmt->fetch(PDO::FETCH_ASSOC)['wishlist'] > 0;

        // 6. Evaluations
        $stmt = $this->connexion->prepare(
            "SELECT e.note, e.avis, u.nom, u.prenom, u.id_compte
             FROM evaluation e
             JOIN utilisateur u ON e.id_compte = u.id_compte
             WHERE e.id_offre = :id");
        $stmt->bindParam(':id', $id_offre);
        $stmt->execute();
        $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Déjà évalué ?
        $stmt = $this->connexion->prepare(
            "SELECT COUNT(*) AS evalue
             FROM evaluation WHERE id_compte = :id_compte AND id_offre = :id_offre");
        $stmt->bindParam(':id_compte', $user_id);
        $stmt->bindParam(':id_offre', $id_offre);
        $stmt->execute();
        $a_evalue = $stmt->fetch(PDO::FETCH_ASSOC)['evalue'] > 0;

        return compact('offre', 'competences', 'secteurs', 'postule', 'wishlist', 'evaluations', 'a_evalue');
    }
    
    public function deleteOffer($id) {
        try {
            $sql = "DELETE FROM offre WHERE id_offre = :id";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'offre: " . $e->getMessage());
            return false;
        }
    }
    
    public function searchOffers($company, $location, $searchCompetences, $page, $itemsPerPage) {
        // Construire la requête SQL de base
        $sqlCount = "SELECT COUNT(DISTINCT o.id_offre) FROM offre o 
        JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
        JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
        JOIN ville v ON ad.Id_ville = v.Id_ville";
    
        $sqlOffres = "SELECT DISTINCT o.*, e.nom AS nom_entreprise, v.nom_ville 
        FROM offre o 
        JOIN entreprise e ON o.Id_entreprise = e.Id_entreprise
        JOIN adresse ad ON e.Id_adresse = ad.Id_adresse
        JOIN ville v ON ad.Id_ville = v.Id_ville";
    
        // Conditions pour les compétences
        if (!empty($searchCompetences)) {
            $sqlCount .= " JOIN contenir co ON o.id_offre = co.id_offre
                          JOIN competence c ON co.Id_competence = c.Id_competence";
    
            $sqlOffres .= " JOIN contenir co ON o.id_offre = co.id_offre
                           JOIN competence c ON co.Id_competence = c.Id_competence";
        }
    
        // Ajouter les conditions de recherche
        $whereConditions = [];
        $params = [];
    
        if (!empty($company)) {
            $whereConditions[] = "e.nom = :company";
            $params[':company'] = $company;
        }
    
        if (!empty($location)) {
            $whereConditions[] = "(v.nom_ville LIKE :location OR r.nom_region LIKE :location)";
    
            // S'assurer que region est inclus dans la requête
            if (strpos($sqlOffres, "JOIN region r") === false) {
                $sqlOffres = str_replace(
                    "JOIN ville v ON ad.Id_ville = v.Id_ville",
                    "JOIN ville v ON ad.Id_ville = v.Id_ville JOIN region r ON v.Id_region = r.Id_region",
                    $sqlOffres
                );
    
                $sqlCount = str_replace(
                    "JOIN ville v ON ad.Id_ville = v.Id_ville",
                    "JOIN ville v ON ad.Id_ville = v.Id_ville JOIN region r ON v.Id_region = r.Id_region",
                    $sqlCount
                );
            }
    
            $params[':location'] = '%' . $location . '%';
        }
    
        // Recherche par compétences
        if (!empty($searchCompetences)) {
            if (count($searchCompetences) > 1) {
                // Requête pour trouver les offres qui ont toutes les compétences sélectionnées
                $whereConditions[] = "o.id_offre IN (
                    SELECT co2.id_offre
                    FROM contenir co2
                    JOIN competence c2 ON co2.Id_competence = c2.Id_competence
                    WHERE c2.nom IN (" . implode(', ', array_map(function ($i) {
                    return ':comp_in_' . $i; }, array_keys($searchCompetences))) . ")
                    GROUP BY co2.id_offre
                    HAVING COUNT(DISTINCT c2.Id_competence) = " . count($searchCompetences) . "
                )";
    
                // Ajouter les paramètres pour la sous-requête
                foreach ($searchCompetences as $index => $competence) {
                    $params[':comp_in_' . $index] = $competence;
                }
            } else {
                // Si une seule compétence, la requête est plus simple
                $competenceParam = ':competence0';
                $whereConditions[] = "c.nom = $competenceParam";
                $params[$competenceParam] = reset($searchCompetences);
            }
        }
    
        // Assembler la clause WHERE si nécessaire
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(' AND ', $whereConditions);
            $sqlCount .= $whereClause;
            $sqlOffres .= $whereClause;
        }
    
        // Ajouter l'ordre de tri par date décroissante (plus récentes en premier)
        $sqlOffres .= " ORDER BY o.date_publication DESC";
    
        // Ajouter la pagination
        $sqlOffres .= " LIMIT :offset, :limit";
        $params[':offset'] = ($page - 1) * $itemsPerPage;
        $params[':limit'] = $itemsPerPage;
    
        try {
            // Exécuter la requête pour compter le nombre total d'offres
            $stmtCount = $this->connexion->prepare($sqlCount);
    
            // Lier les paramètres pour la requête de comptage
            foreach ($params as $key => $value) {
                // Ne pas lier les paramètres de pagination pour la requête COUNT
                if ($key !== ':offset' && $key !== ':limit') {
                    $stmtCount->bindValue($key, $value);
                }
            }
    
            $stmtCount->execute();
            $totalOffres = $stmtCount->fetchColumn();
            $totalPages = ceil($totalOffres / $itemsPerPage);
    
            // Exécuter la requête pour récupérer les offres de la page actuelle
            $stmtOffres = $this->connexion->prepare($sqlOffres);
    
            // Lier tous les paramètres
            foreach ($params as $key => $value) {
                if ($key === ':offset' || $key === ':limit') {
                    $stmtOffres->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmtOffres->bindValue($key, $value);
                }
            }
    
            $stmtOffres->execute();
            $offres = $stmtOffres->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les compétences pour chaque offre
            foreach ($offres as &$offre) {
                $offre['competences'] = $this->getCompetencesForOffer($offre['id_offre']);
            }
    
            return [
                'offres' => $offres,
                'total' => $totalOffres,
                'totalPages' => $totalPages
            ];
    
        } catch (PDOException $e) {
            // En cas d'erreur, log et retourne un tableau vide
            error_log("Erreur lors de la recherche d'offres: " . $e->getMessage());
            return [
                'offres' => [],
                'total' => 0,
                'totalPages' => 0
            ];
        }
    }

    public function processApplication($id_offre, $id_compte, $files) {
        // Vérifie l'existence, traite les uploads, et insère la postulation
        try {
            // Offre existe ?
            $stmt = $this->connexion->prepare("SELECT COUNT(*) FROM offre WHERE id_offre = :id");
            $stmt->bindParam(':id', $id_offre, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                return ['success' => false, 'message' => "L'offre n'existe pas."];
            }
            // Déjà postulé ?
            $stmt = $this->connexion->prepare("SELECT COUNT(*) FROM postuler WHERE id_compte = :id_compte AND id_offre = :id_offre");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => "Tu as déjà postulé à cette offre."];
            }
            // Upload
            $upload_dir = __DIR__ . "/../uploads/candidatures/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            if (!is_writable($upload_dir)) return ['success' => false, 'message' => "Répertoire d'upload inaccessible."];

            // CV
            if (!isset($files['cv']) || $files['cv']['error'] !== 0) return ['success' => false, 'message' => "Le CV est obligatoire."];
            if ($files['cv']['type'] !== 'application/pdf') return ['success' => false, 'message' => "Le CV doit être un PDF."];
            $cv_filename = $id_compte . '_' . $id_offre . '_cv_' . time() . '.pdf';
            if (!move_uploaded_file($files['cv']['tmp_name'], $upload_dir . $cv_filename)) return ['success' => false, 'message' => "Erreur d'upload du CV."];

            // Lettre
            if (!isset($files['lettre_motivation']) || $files['lettre_motivation']['error'] !== 0) {
                unlink($upload_dir . $cv_filename);
                return ['success' => false, 'message' => "Lettre de motivation obligatoire."];
            }
            if ($files['lettre_motivation']['type'] !== 'application/pdf') {
                unlink($upload_dir . $cv_filename);
                return ['success' => false, 'message' => "La lettre doit être un PDF."];
            }
            $lettre_filename = $id_compte . '_' . $id_offre . '_lettre_' . time() . '.pdf';
            if (!move_uploaded_file($files['lettre_motivation']['tmp_name'], $upload_dir . $lettre_filename)) {
                unlink($upload_dir . $cv_filename);
                return ['success' => false, 'message' => "Erreur d'upload de la lettre."];
            }

            // Insert
            $stmt = $this->connexion->prepare("INSERT INTO postuler (id_compte, id_offre, cv_path, lettre_motivation_path, date_candidature) VALUES (:id_compte, :id_offre, :cv_path, :lettre_path, NOW())");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->bindParam(':cv_path', $cv_filename);
            $stmt->bindParam(':lettre_path', $lettre_filename);
            $stmt->execute();

            return ['success' => true, 'message' => "Ta candidature a bien été envoyée !"];
        } catch(Exception $e) {
            return ['success' => false, 'message' => "Erreur lors de la candidature: " . $e->getMessage()];
        }
    }

    public function addEvaluation($id_offre, $id_compte, $note, $avis) {
        try {
            // Offre existe ?
            $stmt = $this->connexion->prepare("SELECT 1 FROM offre WHERE id_offre = :id_offre");
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->execute();
            if (!$stmt->fetch()) return ['success' => false, 'message' => "Offre introuvable."];

            // Déjà évalué ?
            $stmt = $this->connexion->prepare("SELECT 1 FROM evaluation WHERE id_compte = :id_compte AND id_offre = :id_offre");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->execute();
            if ($stmt->fetch()) return ['success' => false, 'message' => "Tu as déjà évalué cette offre."];

            // Note valide ?
            $notes_valides = ['Excellent', 'Très bien', 'Bien', 'Moyen', 'À éviter'];
            if (!in_array($note, $notes_valides)) return ['success' => false, 'message' => "Note invalide."];

            // Ajout
            $stmt = $this->connexion->prepare("INSERT INTO evaluation (id_compte, id_offre, note, avis) VALUES (:id_compte, :id_offre, :note, :avis)");
            $stmt->bindParam(':id_compte', $id_compte, PDO::PARAM_STR);
            $stmt->bindParam(':id_offre', $id_offre);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':avis', $avis);
            $stmt->execute();

            return ['success' => true, 'message' => "Merci, ton évaluation est prise en compte !"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Erreur: " . $e->getMessage()];
        }
    }
}
?>