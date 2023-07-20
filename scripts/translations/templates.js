const terms = [
  {
    en: 'For security reasons, you must enable SSL to use this module.',
    fr: 'Pour des raisons de sécurité, vous devez activer SSL pour utiliser ce module.',
    domains: [
      'infos',
    ],
  },
  {
    en: 'link',
    fr: 'ce lien',
    domains: [
      'infos',
    ],
  },
  {
    en: 'Open preferences to activate SSL',
    fr: 'Ouvrir les préférences pour activer SSL',
    domains: [
      'infos',
    ],
  },
  {
    en: 'This can be done in general preferences available in the left menu or by following this',
    fr: 'Cela peut être fait dans les préférences générales disponibles dans le menu de gauche ou en suivant',
    domains: [
      'infos',
    ],
  },
  {
    en: 'This module allows you to accept secure payments by card.',
    fr: "Ce module vous permet d'accepter les paiements par carte de façon simple et sécurisée.",
    domains: [
      'infos',
    ],
  },
  {
    en: "You will be redirected to our partner's portal to make the payment.",
    fr: 'Vous allez être redirigé vers le portail de notre partenaire de paiement.',
    domains: [
      'option',
    ],
  },
];

module.exports = terms.map((texts) => {
  const domains = texts.domains;

  delete texts.domains;

  return {
    texts,
    domains,
  };
});
