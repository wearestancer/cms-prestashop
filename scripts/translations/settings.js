const terms = [
  {
    en: 'https://manage.stancer.com/en/developers',
    fr: 'https://manage.stancer.com/fr/developpeurs',
  },
  {
    en: '"%s" is invalid.',
    fr: '"%s" est invalide.',
  },
  {
    en: '"%s" is invalid, please provide a correct key.',
    fr: '"%s" est invalide, merci de fournir une clé valide.',
  },
  {
    en: 'Add scheme logo on reused card',
    fr: 'Ajouter le logo du réseau sur les cartes enregistrées',
  },
  {
    en: 'Allow customers to reuse cards',
    fr: "Autoriser l'enregistrement des cartes",
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
    en: 'Create an order for every payment',
    fr: 'Créer une commande pour chaque paiement',
  },
  {
    en: 'Currency of the cart',
    fr: 'Devise du panier',
  },
  {
    en: 'Display',
    fr: 'Affichage',
  },
  {
    en: 'Inside the page (recommended)',
    fr: 'Inclus dans la page (recommandé)',
  },
  {
    en: 'Inside the page, including authenticated payment',
    fr: 'Inclus dans la page, incluant les paiements authentifiés',
  },
  {
    en: 'Live',
    fr: 'Production',
  },
  {
    en: 'Mode',
    fr: 'Mode',
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
    en: 'Payment option logo',
    fr: "Logo de l'option de paiement",
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
    en: 'Test',
    fr: 'Test',
  },
  {
    en: 'Total amount',
    fr: 'Montant total',
  },
  {
    en: 'When active, an order will be created for failed payments.',
    fr: 'Avec cette option activée, une commande sera créée lors des échecs de paiements.',
  },
  {
    en: 'You cannot switch to live mode while an error is occurring with the API keys.',
    fr: 'Vous ne pouvez pas passer en mode production tant que vous avez une erreur avec les clés API.',
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
