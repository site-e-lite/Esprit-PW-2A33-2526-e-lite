# 🤖 Documentation de l'Intégration IA - Projet e-lite

Ce document explique le fonctionnement, la configuration et les fonctionnalités de l'intelligence artificielle intégrée à la plateforme de gestion de forum.

## 🛠️ Technologie Utilisée

Le projet utilise l'API de **Groq** avec le modèle **Llama 3.1 (8b-instant)**. Ce choix garantit :
- **Vitesse :** Réponses quasi-instantanées. 
- **Fiabilité :** Une excellente compréhension du contexte éducatif.
- **Quota :** Un accès gratuit généreux par rapport aux autres fournisseurs.

---

## 🌟 Fonctionnalités IA

### 1. Modération Automatique du Contenu
Avant chaque ajout de forum ou de message, l'IA analyse le texte :
- **Détection de toxicité :** Bloque les insultes graves et les contenus illégaux (Risque "High").
- **Filtre hybride :** Combine une liste locale de "mots interdits" (pour une sécurité immédiate) et l'IA (pour le contexte).
- **Tolérance intelligente :** Les contenus suspects mais non-offensants (Risque "Medium") sont autorisés pour éviter les faux positifs.

### 2. Assistant IA (Chatbot)
Un assistant interactif est disponible dans le FrontOffice :
- **Spécialisation :** Configuré pour ne répondre qu'aux questions liées à l'éducation, à la programmation et à la plateforme.
- **Suggestions hybrides :** Si l'IA ne trouve pas de réponse ou si le quota est atteint, le système suggère automatiquement des discussions similaires trouvées dans la base de données SQL.

### 3. Résumé de Discussions
Dans chaque forum, un bouton "Résumer par IA" permet :
- De lire les 10 derniers messages.
- De générer un résumé concis (2-3 phrases) pour comprendre rapidement l'essentiel du fil de discussion.

---

## ⚙️ Configuration

Toute la configuration se trouve dans le fichier `config.php` :

```php
// Clé API Groq Cloud
const GROQ_API_KEY = 'gsk_...'; 
```

Le contrôleur principal gérant la logique est `Controller/ChatbotController.php`.

---

## 🛡️ Robustesse et Fallbacks

Le système est conçu pour rester fonctionnel même en cas de problème technique :
- **Erreur API / Quota :** Si Groq ne répond pas, la modération passe en mode "permissif" pour ne pas bloquer les utilisateurs légitimes, et le chatbot propose des recherches locales.
- **Sécurité locale :** Une liste de mots vulgaires est vérifiée en PHP avant même d'appeler l'IA, économisant ainsi votre quota API.

---

## 📝 Maintenance
Pour changer de modèle ou ajuster la sévérité de la modération, modifiez les paramètres dans `ChatbotController::callGroqAPI`.
