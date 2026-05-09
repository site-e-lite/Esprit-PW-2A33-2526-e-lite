# Requirements Document

## Introduction

Ce document définit les exigences pour trois fonctionnalités avancées d'un système e-learning : le suivi de progression (Progress Tracking), le système de notation (Rating System), et le système de certificats (Certificate System). Ces fonctionnalités permettront aux utilisateurs de suivre leur avancement dans les cours, d'évaluer la qualité des cours, et d'obtenir des certificats de réussite.

Le système doit respecter strictement l'architecture MVC existante, séparer la logique métier de la présentation, et garantir la sécurité des données utilisateur.

## Glossary

- **Progress_Tracker**: Le système qui gère le suivi de progression des utilisateurs dans les cours
- **Rating_System**: Le système qui gère les évaluations (notes) des cours par les utilisateurs
- **Certificate_Generator**: Le système qui génère et gère les certificats de réussite
- **User**: Un utilisateur inscrit dans la plateforme e-learning
- **Course**: Un cours disponible dans la plateforme
- **Progress_Record**: Un enregistrement de progression pour un utilisateur dans un cours spécifique
- **Rating_Record**: Un enregistrement d'évaluation d'un cours par un utilisateur
- **Certificate_Record**: Un enregistrement de certificat obtenu par un utilisateur
- **Progress_Percent**: Le pourcentage de progression d'un utilisateur dans un cours (0-100)
- **Rating_Value**: La note attribuée à un cours (1-5 étoiles)
- **Average_Rating**: La moyenne des notes d'un cours
- **Database**: La base de données MySQL e_lite
- **MVC_Controller**: Un contrôleur dans l'architecture MVC qui gère la logique métier
- **MVC_Model**: Un modèle dans l'architecture MVC qui représente les données
- **MVC_View**: Une vue dans l'architecture MVC qui gère l'affichage
- **Prepared_Statement**: Une requête SQL préparée pour prévenir les injections SQL

## Requirements

### Requirement 1: Progress Tracking - Création et Mise à Jour de la Progression

**User Story:** En tant qu'utilisateur, je veux que ma progression dans un cours soit automatiquement suivie, afin de pouvoir reprendre là où je me suis arrêté et visualiser mon avancement.

#### Acceptance Criteria

1. THE Progress_Tracker SHALL créer un Progress_Record avec progress_percent à 0 et last_accessed à la date actuelle WHEN un User accède à un Course pour la première fois
2. THE Progress_Tracker SHALL mettre à jour le champ last_accessed avec la date et heure actuelles WHEN un User accède à un Course existant dans ses Progress_Record
3. THE Progress_Tracker SHALL empêcher la création de Progress_Record en double pour la même combinaison User et Course
4. THE Progress_Tracker SHALL utiliser des Prepared_Statement pour toutes les opérations sur la table progress
5. THE Progress_Tracker SHALL valider que progress_percent reste dans l'intervalle 0-100 avant toute mise à jour

### Requirement 2: Progress Tracking - Incrémentation de la Progression

**User Story:** En tant qu'utilisateur, je veux pouvoir marquer des leçons comme complétées, afin que ma progression augmente automatiquement.

#### Acceptance Criteria

1. WHEN un User marque une leçon comme complétée, THE Progress_Tracker SHALL incrémenter progress_percent de 10 points
2. THE Progress_Tracker SHALL limiter progress_percent à un maximum de 100 même si plusieurs incrémentations sont effectuées
3. THE Progress_Tracker SHALL mettre à jour last_accessed à la date et heure actuelles WHEN progress_percent est incrémenté
4. THE Progress_Tracker SHALL retourner un message d'erreur IF progress_percent dépasse 100 avant limitation
5. THE MVC_Controller SHALL valider l'identité du User avant d'autoriser l'incrémentation de progression

### Requirement 3: Progress Tracking - Affichage de la Progression

**User Story:** En tant qu'utilisateur, je veux voir une barre de progression visuelle pour chaque cours, afin de visualiser rapidement mon avancement.

#### Acceptance Criteria

1. THE MVC_View SHALL afficher une barre de progression visuelle montrant progress_percent pour chaque Course d'un User
2. THE MVC_View SHALL afficher la date de last_accessed au format lisible (ex: "Dernière activité: 15 janvier 2024")
3. THE MVC_View SHALL utiliser une représentation graphique (barre HTML/CSS) proportionnelle à progress_percent
4. THE MVC_View SHALL afficher "0%" WHEN aucun Progress_Record n'existe pour un Course
5. THE MVC_View SHALL séparer strictement le code HTML de la logique métier (pas de requêtes SQL dans la vue)

### Requirement 4: Rating System - Soumission et Modification de Notes

**User Story:** En tant qu'utilisateur, je veux pouvoir noter un cours de 1 à 5 étoiles, afin de partager mon évaluation de la qualité du cours.

#### Acceptance Criteria

1. WHEN un User soumet une note pour un Course, THE Rating_System SHALL créer un Rating_Record avec rating entre 1 et 5
2. THE Rating_System SHALL permettre à un User de modifier sa note existante pour un Course
3. THE Rating_System SHALL empêcher un User de créer plusieurs Rating_Record pour le même Course
4. THE Rating_System SHALL valider que rating est un entier entre 1 et 5 inclus avant insertion ou mise à jour
5. THE Rating_System SHALL utiliser des Prepared_Statement pour toutes les opérations sur la table ratings
6. THE MVC_Controller SHALL vérifier que le User est authentifié avant d'accepter une note

### Requirement 5: Rating System - Calcul de la Moyenne des Notes

**User Story:** En tant qu'utilisateur, je veux voir la note moyenne d'un cours, afin d'évaluer sa qualité globale avant de m'inscrire.

#### Acceptance Criteria

1. THE Rating_System SHALL calculer Average_Rating en faisant la moyenne de tous les Rating_Record pour un Course donné
2. THE Rating_System SHALL retourner 0 ou NULL WHEN aucun Rating_Record n'existe pour un Course
3. THE Rating_System SHALL arrondir Average_Rating à une décimale (ex: 4.3)
4. THE Rating_System SHALL recalculer Average_Rating à chaque fois qu'un Rating_Record est créé ou modifié
5. THE MVC_Controller SHALL fournir Average_Rating à la MVC_View sans exposer les détails de calcul

### Requirement 6: Rating System - Affichage des Notes

**User Story:** En tant qu'utilisateur, je veux voir un formulaire de notation intuitif et la note moyenne d'un cours, afin de facilement évaluer et consulter les évaluations.

#### Acceptance Criteria

1. THE MVC_View SHALL afficher un formulaire de sélection avec des options de 1 à 5 étoiles pour soumettre une note
2. THE MVC_View SHALL pré-remplir le formulaire avec la note existante du User IF un Rating_Record existe déjà
3. THE MVC_View SHALL afficher Average_Rating avec le nombre total de Rating_Record (ex: "4.3/5 (127 avis)")
4. THE MVC_View SHALL afficher "Aucune note" WHEN Average_Rating est 0 ou NULL
5. THE MVC_View SHALL séparer strictement le code HTML de la logique métier

### Requirement 7: Certificate System - Génération de Certificats

**User Story:** En tant qu'utilisateur, je veux recevoir automatiquement un certificat quand je termine un cours à 100%, afin de valoriser ma réussite.

#### Acceptance Criteria

1. WHEN progress_percent atteint 100 pour un User et un Course, THE Certificate_Generator SHALL créer un Certificate_Record avec date_obtained à la date actuelle
2. THE Certificate_Generator SHALL empêcher la création de Certificate_Record en double pour la même combinaison User et Course
3. THE Certificate_Generator SHALL vérifier que progress_percent est exactement 100 avant de créer un Certificate_Record
4. THE Certificate_Generator SHALL utiliser des Prepared_Statement pour toutes les opérations sur la table certificates
5. THE Certificate_Generator SHALL créer le Certificate_Record dans la même transaction que la mise à jour de progress_percent à 100

### Requirement 8: Certificate System - Affichage des Certificats

**User Story:** En tant qu'utilisateur, je veux consulter mes certificats obtenus avec les détails du cours et la date, afin de conserver une preuve de ma réussite.

#### Acceptance Criteria

1. THE MVC_View SHALL afficher une page certificate.php listant tous les Certificate_Record d'un User
2. THE MVC_View SHALL afficher pour chaque Certificate_Record: le nom du User, le titre du Course, et date_obtained au format lisible
3. THE MVC_View SHALL afficher "Aucun certificat obtenu" WHEN aucun Certificate_Record n'existe pour le User
4. THE MVC_View SHALL permettre l'accès à certificate.php uniquement aux User authentifiés
5. THE MVC_View SHALL séparer strictement le code HTML de la logique métier

### Requirement 9: Architecture MVC - Séparation des Responsabilités

**User Story:** En tant que développeur, je veux que le code respecte strictement l'architecture MVC, afin de maintenir la qualité et la maintenabilité du projet.

#### Acceptance Criteria

1. THE MVC_Model SHALL contenir uniquement les définitions de classes et propriétés (Progress, Rating, Certificate)
2. THE MVC_Controller SHALL contenir toute la logique métier, les requêtes SQL, et la validation des données
3. THE MVC_View SHALL contenir uniquement le code HTML et l'affichage des données fournies par le MVC_Controller
4. THE MVC_View SHALL ne jamais contenir de requêtes SQL directes ou de logique métier
5. THE MVC_Controller SHALL utiliser des Prepared_Statement pour toutes les interactions avec la Database

### Requirement 10: Sécurité - Protection des Données

**User Story:** En tant qu'administrateur système, je veux que toutes les entrées utilisateur soient sécurisées, afin de protéger la plateforme contre les attaques.

#### Acceptance Criteria

1. THE MVC_Controller SHALL utiliser des Prepared_Statement pour toutes les requêtes SQL impliquant des données utilisateur
2. THE MVC_Controller SHALL valider tous les paramètres d'entrée (types, plages de valeurs) avant traitement
3. THE MVC_Controller SHALL échapper ou filtrer toutes les données avant affichage dans la MVC_View
4. THE MVC_Controller SHALL vérifier l'authentification du User avant toute opération de modification de données
5. THE MVC_Controller SHALL retourner des messages d'erreur génériques sans exposer de détails techniques sensibles

### Requirement 11: Base de Données - Structure des Tables

**User Story:** En tant qu'administrateur de base de données, je veux que les nouvelles tables soient correctement structurées, afin d'assurer l'intégrité et les performances des données.

#### Acceptance Criteria

1. THE Database SHALL contenir une table progress avec les colonnes: id (INT AUTO_INCREMENT PRIMARY KEY), user_id (INT NOT NULL), course_id (INT NOT NULL), progress_percent (INT DEFAULT 0), last_accessed (DATETIME)
2. THE Database SHALL contenir une table ratings avec les colonnes: id (INT AUTO_INCREMENT PRIMARY KEY), user_id (INT NOT NULL), course_id (INT NOT NULL), rating (INT NOT NULL)
3. THE Database SHALL contenir une table certificates avec les colonnes: id (INT AUTO_INCREMENT PRIMARY KEY), user_id (INT NOT NULL), course_id (INT NOT NULL), date_obtained (DATETIME NOT NULL)
4. THE Database SHALL définir une contrainte UNIQUE sur (user_id, course_id) pour les tables progress, ratings, et certificates
5. THE Database SHALL définir des clés étrangères pour user_id et course_id référençant les tables appropriées

### Requirement 12: Intégration - Compatibilité avec le Système Existant

**User Story:** En tant que développeur, je veux que les nouvelles fonctionnalités s'intègrent sans casser le code existant, afin de maintenir la stabilité de la plateforme.

#### Acceptance Criteria

1. THE Progress_Tracker SHALL réutiliser la classe Config existante pour la connexion à la Database
2. THE Rating_System SHALL réutiliser la classe Config existante pour la connexion à la Database
3. THE Certificate_Generator SHALL réutiliser la classe Config existante pour la connexion à la Database
4. THE MVC_Controller SHALL suivre les mêmes conventions de nommage et structure que CourseController et EnrollmentController existants
5. THE MVC_View SHALL réutiliser les fichiers header.php et footer.php existants dans View/includes
