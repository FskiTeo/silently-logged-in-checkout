Permet une connexion silencieuse et automatique des utilisateurs lors du passage en caisse WooCommerce via vérification par email et code OTP.

== Description ==

Le plugin "Silently Logged In Checkout" simplifie le processus de connexion pour vos clients WooCommerce. Au lieu de forcer les utilisateurs non connectés à créer manuellement un compte ou à utiliser un formulaire de connexion complexe, ce plugin leur permet de :

1. Entrer simplement leur adresse email
2. Recevoir un code OTP (One-Time Password) à 6 chiffres par email
3. Vérifier le code reçu
4. Être connectés et créer un compte WooCommerce automatiquement
5. Accéder directement à la page de paie

**Avantages :**
- Expérience utilisateur fluide et sans friction
- Les clients n'ont pas besoin de choisir un mot de passe (réduit les oublis)
- Compatible avec les thèmes WordPress moderne (y compris FSE - Full Site Editing)
- Configuration simple via le menu d'administration WooCommerce
- Emails personnalisés avec le nom de votre boutique
- Gestion sécurisée des codes OTP avec transients WordPress

== Installation ==

1. Téléchargez le fichier ZIP du plugin
2. Allez à **Extensions > Ajouter** dans votre tableau de bord WordPress
3. Cliquez sur **Importer une extension** et sélectionnez le fichier ZIP
4. Activez le plugin
5. Accédez à **WooCommerce > Silently Logged In** pour configurer les pages

**Ou manuellement :**
1. Décompressez le fichier ZIP
2. Uploadez le dossier `silently-loggedin-checkout` dans `/wp-content/plugins/`
3. Activez le plugin depuis le menu Extensions
4. Configurez le plugin (voir section Configuration)

== Configuration ==

=== Paramètres initiaux ===

Après activation, rendez-vous dans **WooCommerce > Silently Logged In Checkout** pour configurer :

**1. Page de formulaire email**
   - Sélectionnez la page qui contiendra le formulaire `[slc_email_prompt]`
   - Cette page sera accessible après désactivation d'une session utilisateur
   - Les utilisateurs y entreront leur adresse email pour lancer le processus

**2. Page de vérification OTP**
   - Sélectionnez la page qui contiendra le formulaire `[slc_otp_verify]`
   - Cette page affichera un formulaire pour entrer le code OTP reçu par email
   - Doit être la même pour tous les clients afin assurer la cohérence

**3. Page de redirection (utilisateurs connectés)**
   - Sélectionnez la page où rediriger les utilisateurs **déjà connectés** qui visitent l'une des deux pages ci-dessus
   - Généralement, c'est la page de paie WooCommerce ou votre page d'accueil
   - Si non configurée, redirection automatique vers la page de paie WooCommerce par défaut

=== Créer les pages ===

Vous devez créer au moins deux pages WordPress contenant les shortcodes :

**Page 1 : Formulaire email**
- Création : **Pages > Ajouter**
- Titre suggéré : "Connexion rapide" ou "Vérification email"
- Contenu : Ajoutez simplement `[slc_email_prompt]` dans l'éditeur
- Sauvegardez et notez l'ID de la page
- Sélectionnez-la dans la configuration du plugin

**Page 2 : Formulaire OTP**
- Création : **Pages > Ajouter**
- Titre suggéré : "Vérification du code" ou "Confirmer votre code"
- Contenu : Ajoutez simplement `[slc_otp_verify]` dans l'éditeur
- Sauvegardez et notez l'ID de la page
- Sélectionnez-la dans la configuration du plugin

== Utilisation ==

=== Flux utilisateur normal ===

1. **Utilisateur non connecté** visite la page de paie WooCommerce
2. **WooCommerce** le redirige vers la page de formulaire email (configurable via WooCommerce)
3. **Utilisateur** entre son email et clique sur "Envoyer le code"
4. **Plugin** :
   - Génère un code OTP à 6 chiffres
   - Stocke le code en toute sécurité pendant 10 minutes
   - Envoie un email avec le code
5. **Utilisateur** reçoit l'email et est redirigé vers la page de vérification
6. **Utilisateur** entre son code OTP
7. **Plugin** :
   - Valide le code
   - Crée un compte WooCommerce avec l'email fourni
   - Connecte automatiquement l'utilisateur
8. **Utilisateur** est redirigé vers la page de paie (checkout)
9. **Utilisateur** finalise l'achat en tant qu'utilisateur connecté

=== Personnalisation stylistique ===

Le plugin utilise un fichier CSS pour styliser les formulaires : `public/css/silently-loggedin-checkout-public.css`

**Classes CSS disponibles :**
- `.slc-form-wrapper` : Conteneur principal des formulaires
- `.slc-error` : Messages d'erreur (couleur rouge)
- `input[type="email"]`, `input[type="text"]` : Champs de formulaire
- `button[type="submit"]` : Boutons de soumission

Modifiez directement le fichier CSS ou ajoutez du CSS personnalisé dans votre thème pour adapter l'apparence à l'identité visuelle de votre boutique.

=== Emails ===

Le plugin envoie des emails automatiquement lors de la génération du code OTP.

**Email envoyé :**
- **Objet** : "Votre code de connexion [Nom de la boutique]"
- **Contenu** : 
  - Salutation
  - Code OTP en 6 chiffres
  - Durée de validité (10 minutes)
  - Note de sécurité

L'email est envoyé au format HTML et inclut automatiquement le nom de votre boutique.

== Livrables ==

=== Shortcodes ===

**`[slc_email_prompt]`**
- Affiche le formulaire de saisie d'email
- Aucun paramètre requis
- Utilisez-le dans une page WordPress

**`[slc_otp_verify]`**
- Affiche le formulaire de saisie du code OTP
- Aucun paramètre requis
- Utilisez-le dans une page WordPress
- Redirige automatiquement vers le formulaire email si l'utilisateur accède sans passer par le flux normal

== Questions fréquentes ==

**Q : Quel est le délai d'expiration du code OTP?**
A : 10 minutes. Après ce délai, l'utilisateur doit recommencer depuis le début.

**Q : Combien de fois l'utilisateur peut-il entrer le mauvais code?**
A : 3 tentatives maximum. Après 3 tentatives échouées, le code OTP est invalidé.

**Q : Le plugin fonctionne-t-il avec tous les thèmes WordPress?**
A : Oui, le plugin fonctionne avec tous les thèmes, y compris les thèmes Full Site Editing (FSE). Il génère des pages indépendantes du thème, ce qui signifie que votre en-tête et pied de page personnalisés s'afficheront normalement.

**Q : Les mots de passe des utilisateurs?**
A : Le plugin génère des comptes WooCommerce avec des mots de passe aléatoires ou en utilisant l'email comme unique identifier. Les utilisateurs peuvent modifier leur mot de passe ensuite s'ils le souhaitent.

**Q : Comment puis-je tester le plugin?**
A : 
1. Déconnectez-vous
2. Rendez-vous sur la page de paie
3. Entrez une adresse email valide
4. Vérifiez votre boîte mail pour le code OTP
5. Entrez le code sur la page de vérification

**Q : Que se passe-t-il si un utilisateur déjà connecté visite la page?**
A : Il est automatiquement redirigé vers la page configurée dans les paramètres (par défaut, la page de paie WooCommerce).

== Sécurité ==

- **Codes OTP** : Générés de façon cryptographique et stockés via l'API WordPress Transients
- **Validation CSRF** : Tous les formulaires incluent des vérifications de nonce WordPress
- **Hachage d'email** : Les emails sont hachés (MD5) lors du stockage du code OTP
- **Limitation de tentatives** : Maximum 3 tentatives de code incorrect avant invalidation
- **HTTPS recommandé** : Bien que non obligatoire, HTTPS est fortement recommandé

== Support ==

Pour toute question ou problème :
- Consultez la section "Questions fréquentes"
- Vérifiez que WooCommerce est activé et mis à jour
- Vérifiez que les pages sont correctement configurées dans le menu admin
- Assurez-vous que vos serveur de messagerie WordPress est correctement configuré pour envoyer des emails

== Historique des modifications ==

= 1.0.0 =
- Version initiale
- Formulaires de connexion par email et OTP
- Création automatique de comptes WooCommerce
- Redirection intelligente des utilisateurs connectés
- Configuration via le panneau d'administration WooCommerce

== Licence ==

Ce plugin est sous licence GPL-2.0+. Consultez LICENSE.txt pour plus de détails.

== Auteur ==

Développé par Téo F
