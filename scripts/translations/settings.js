const terms = [
  {
    en: 'https://www.stancer.com/documentation/api/#test-cards',
    fr: 'https://www.stancer.com/documentation/api/#test-cards',
  },
  {
    en: 'https://manage.stancer.com/en/developers',
    fr: 'https://manage.stancer.com/fr/developpeurs',
  },
  {
    en: '%s is invalid, please provide a correct key.',
    fr: '%s est invalide, merci de fournir une clé valide.',
  },
  {
    en: 'API keys',
    fr: "Clés d'API",
  },
  {
    en: 'Are you sure you want to uninstall?',
    fr: 'Êtes-vous sur de vouloir désinstaller le module ?',
  },
  {
    en: 'Authentication limit',
    fr: 'Limite pour les paiements authentifiés',
  },
  {
    en: 'Back to list',
    fr: 'Retour à la liste',
  },
  {
    en: 'Cart identifier',
    fr: 'Identifiant du panier',
  },
  {
    en: 'Check the documentation to find %s.',
    fr: 'Regardez la documentation pour trouver %s.',
  },
  {
    en: 'Currency of the cart',
    fr: 'Device du panier',
  },
  {
    en: 'Display',
    fr: 'Affichage',
  },
  {
    en: 'In test mode, no payment will really send to a bank, only test card can be used.',
    fr: 'En mode test, aucun paiement ne sera envoyé à une banque, seuls les cartes de test sont utilisables.',
  },
  {
    en: 'Inside the page (recommanded)',
    fr: 'Inclus dans la page (recommandé)',
  },
  {
    en: 'Inside the page, including authenticated payment',
    fr: 'Inclus dans la page, incluant les paiements authentifiés',
  },
  {
    en: 'Leave blank if you do not wish to authenticate, at zero all payments will be authenticated.',
    fr: 'Laissez vide si vous ne souhaitez pas faire de paiement authentifié, si vous le placez à zéro tous les paiements seront authentifiés.',
  },
  {
    en: 'mandatory in live mode',
    fr: 'obligatoire uniquement en mode production',
  },
  {
    en: 'Minimum amount to trigger an authenticated payment (3DS, Verified by Visa, Mastercard Secure Code...)',
    fr: `Montant minimum pour le déclenchement d'un paiement authentifié (3DS, "Verified by Visa", "Mastercard Secure Code"...)`,
  },
  {
    en: 'Page type',
    fr: "Mode d'affichage",
  },
  {
    en: 'Pay with your %s finishing with %s',
    fr: 'Payer avec votre %s finissant avec %s',
  },
  {
    en: 'Payment option text',
    fr: "Texte de l'option de paiement",
  },
  {
    en: 'Payment description',
    fr: 'Description du paiement',
  },
  {
    en: 'Public live API key',
    fr: 'Clé de production publique',
  },
  {
    en: 'Public test API key',
    fr: 'Clé de test publique',
  },
  {
    en: 'Redirect to an external page',
    fr: 'Rediriger vers une page externe',
  },
  {
    en: 'Save',
    fr: 'Enregistrer',
  },
  {
    en: 'Secret live API key',
    fr: 'Clé de production privée',
  },
  {
    en: 'Secret test API key',
    fr: 'Clé de test privée',
  },
  {
    en: 'Settings',
    fr: 'Réglages',
  },
  {
    en: 'Shop name configured in PrestaShop',
    fr: 'Nom de la boutique configuré dans PrestaShop',
  },
  {
    en: 'Simple payment solution at low prices.',
    fr: 'La solution de paiement simple à petit prix.',
  },
  {
    en: 'Starts with "%s"',
    fr: 'Commence avec "%s"',
  },
  {
    en: 'test cards',
    fr: 'des cartes de tests',
  },
  {
    en: 'Test mode',
    fr: 'Mode test',
  },
  {
    en: 'Total amount',
    fr: 'Montant total',
  },
  {
    en: 'Will be used as description for every payment made.',
    fr: 'Sera utilisé comme description pour chaque paiement.',
  },
  {
    en: 'You can create and recover your API keys on',
    fr: 'Vous pouvez créer et récupérer vos clés API sur',
  },
  {
    en: 'You can not pass to live mode until an error occur with API keys.',
    fr: 'Vous ne pouvez pas passer en mode "live" tant que vous avez une erreur avec les clés API.',
  },
  {
    en: 'You may use simple variables, click to see.',
    fr: 'Vous pouvez utiliser des variables, cliquez ici pour avoir la liste.',
  },
  {
    en: 'You must configure your keys before testing this module.',
    fr: 'Vous devez configurer vos clés avant de tester ce module.',
  },
  {
    en: 'You must configure your production keys before using this module.',
    fr: "Vous devez configurer vos clés de production avant d'utiliser ce module.",
  },
];

module.exports = terms.map((texts) => {
  const domains = texts.domains || [
    'core',
  ];

  delete texts.domains;

  return {
    texts,
    domains,
  };
});
