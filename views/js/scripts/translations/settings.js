/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const terms = [
  {
    en: "Simple payment solution at low prices.",
    fr: "La solution de paiement simple à petit prix.",
  },
  {
    en: "https://manage.stancer.com/en/sign-up",
    fr: "https://manage.stancer.com/fr/inscription",
  },
  {
    en: "Are you sure you want to uninstall?",
    fr: "Êtes-vous sur de vouloir désinstaller le module ?",
  },
  {
    en: 'Starts with "%s"',
    fr: 'Commence avec "%s"',
  },
  {
    en: "Public live API key",
    fr: 'Clé API publique "live"',
  },
  {
    en: "Secret live API key",
    fr: 'Clé API secrète "live"',
  },
  {
    en: "Public test API key",
    fr: 'Clé API publique "test"',
  },
  {
    en: "Secret test API key",
    fr: 'Clé API secrète "test"',
  },
  {
    en: "Page type",
    fr: "Mode d'affichage",
  },
  {
    en: "Redirect to an external page",
    fr: "Rediriger vers une page externe",
  },
  {
    en: "Inside the page",
    fr: "Inclus dans la page",
  },
  {
    en: "Authentication limit",
    fr: "Limite pour les paiements authentifiés",
  },
  {
    en: "Minimum amount to trigger an authenticated payment (3DS, Verified by Visa, Mastercard Secure Code...)",
    fr: `Montant minimum pour le déclenchement d'un paiement authentifié (3DS, "Verified by Visa", "Mastercard Secure Code"...)`,
  },
  {
    en: "Leave blank if you do not wish to authenticate, at zero all payments will be authenticated.",
    fr: "Laissez vide si vous ne souhaitez pas faire de paiement authentifié, si vous le placez à zéro tous les paiements seront authentifiés.",
  },
  {
    en: "Payment description",
    fr: "Description du paiement",
  },
  {
    en: "Will be used as description for every payment made, and will be visible to your customer in redirect mode.",
    fr: 'Sera utilisé comme description pour chaque paiement, celle-ci sera visible pour vos clients en mode "redirection".',
  },
  {
    en: "You may use simple variables, click to see.",
    fr: "Vous pouvez utiliser des variables, cliquez ici pour avoir la liste.",
  },
  {
    en: "Key to secure web calls on scheduler tasks",
    fr: "Clé pour sécuriser les appels web sur les taches planifiées",
  },
  {
    en: "%s is invalid, please provide a correct key.",
    fr: "%s est invalide, merci de fournir une clé valide.",
  },
  {
    en: "You can not pass to live mode until an error occur with API keys.",
    fr: 'Vous ne pouvez pas passer en mode "live" tant que vous avez une erreur avec les clés API.',
  },
  {
    en: "You can create your API keys on",
    fr: "Vous pouvez créer vos clés API sur",
  },
  {
    en: "Keys",
    fr: "Clés",
  },
  {
    en: "Settings",
    fr: "Réglages",
  },
  {
    en: "Test mode",
    fr: "Mode test",
  },
  {
    en: "mandatory in live mode",
    fr: "obligatoire uniquement en mode production",
  },
  {
    en: "In test mode, no payment will really send to a bank, only test card can be used.",
    fr: "En mode test, aucun paiement ne sera envoyé à une banque, seuls les cartes de test sont utilisables.",
  },
  {
    en: "Check the documentation to find %s.",
    fr: "Regardez la documentation pour trouver %s.",
  },
  {
    en: "test cards",
    fr: "des cartes de tests",
  },
  {
    en: "https://www.stancer.com/documentation/api/#test-cards",
    fr: "https://www.stancer.com/documentation/api/#test-cards",
  },
  {
    en: "Save",
    fr: "Enregistrer",
  },
  {
    en: "Back to list",
    fr: "Retour à la liste",
  },
  {
    en: "For security reason, you must enable SSL to use this module.",
    fr: "Pour des raisons de sécurités, vous devez activer SSL pour utiliser ce module.",
  },
  {
    en: "This can be done in general preferences available in the left menu or by following this",
    fr: "Cela peux être fait dans les préférences générales disponible dans le menu de gauche ou en suivant",
  },
  {
    en: "link",
    fr: "ce lien",
  },
  {
    en: "Open preferences to activate SSL",
    fr: "Ouvrir les préférences pour activer SSL",
  },
  {
    en: "You must configure your production keys before using this module.",
    fr: "Vous devez configurer vos clés de production avant d'utiliser ce module.",
  },
  {
    en: "You must configure your keys before testing this module.",
    fr: "Vous devez configurer vos clés avant de tester ce module.",
  },
  {
    en: "Shop name configured in PrestaShop",
    fr: "Nom de la boutique configuré dans PrestaShop",
  },
  {
    en: "Total amount",
    fr: "Montant total",
  },
  {
    en: "Currency of the cart",
    fr: "Device du panier",
  },
  {
    en: "Cart identifier",
    fr: "Identifiant du panier",
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: ["core", "infos"],
  };
});
