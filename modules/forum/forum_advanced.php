<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/PostCrud.php';

class ForumAdvanced
{
    /**
     * FONCTIONNALITÉ AVANCÉE 1 : Tri intelligent des discussions
     * @param int $forumId
     * @param string $criteria 'recent', 'popular' (nécessite table reponse)
     * @return array
     */
    public static function getSortedPosts($forumId, $criteria = 'recent') {
        // TODO : Implémenter selon critère
        // Exemple simple (récence)
        return PostCrud::getByForum($forumId);
    }

    /**
     * FONCTIONNALITÉ AVANCÉE 2 : Suggestion de réponses (IA par mots-clés)
     * @param string $postContent
     * @return string Suggestion
     */
    public static function suggestReply($postContent) {
        $keywords = ['erreur', 'bug', 'problème', 'aide', 'question'];
        foreach ($keywords as $kw) {
            if (stripos($postContent, $kw) !== false) {
                return "Avez-vous consulté la FAQ ou les ressources du cours ?";
            }
        }
        return "Merci pour votre message. Un formateur vous répondra bientôt.";
    }
}
?>