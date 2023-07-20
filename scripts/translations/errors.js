const terms = [
  {
    en: 'An unknown error occurred while connecting to the payment platform.',
    fr: "Une erreur inconnue s'est produite lors de la connexion à la plateforme de paiement.",
  },
  {
    en: 'Back',
    fr: 'Retour',
    domains: [
      'error',
    ],
  },
  {
    en: 'Configuration error, unknown mode "%s".',
    fr: 'Erreur de configuration, le mode "%s" est inconnu.',
  },
  {
    en: 'If this message persists, please contact the store to resolve this issue as soon as possible.',
    fr: 'Si ce message persiste, veuillez contacter la boutique pour résoudre ce souci le plus vite possible.',
    domains: [
      'error',
    ],
  },
  {
    en: 'Impossible to connect to the payment platform.',
    fr: 'Impossible de se connecter à la plateforme de paiement.',
  },
  {
    en: 'No payment found for this cart.',
    fr: 'Aucun paiement trouvé pour ce panier.',
  },
  {
    en: 'Please contact us to unlock this situation.',
    fr: 'Contactez nous pour débloquer la situation.',
  },
  {
    en: 'Please reconfigure the module or ask the site administrator to do it.',
    fr: "Veuillez reconfigurer le module ou demandez à l'administrateur du site de le faire.",
  },
  {
    en: 'Please wait a minute and try again.',
    fr: "Merci de patienter quelques instants avant de recommencer l'opération.",
  },
  {
    en: 'The payment attempt failed.',
    fr: 'La tentative de paiement a échouée.',
  },
  {
    en: 'The payment platform is currently unavailable.',
    fr: 'La plateforme de paiement est actuellement indisponible.',
  },
  {
    en: 'The payment platform is currently unreacheable.',
    fr: 'La plateforme de paiement est actuellement inaccessible.',
  },
  {
    en: 'This error should be temporary, please try again later.',
    fr: "Cette erreur devrait être temporaire, merci de renouveler l'opération ultérieurement.",
    domains: [
      'error',
    ],
  },
  {
    en: 'This payment method is currently unavailable.',
    fr: 'Ce moyen de paiement est actuellement indisponible.',
  },
  {
    en: 'Your card has not been charged.',
    fr: "Votre carte n'a pas été débitée.",
  },
];

module.exports = terms.map((texts) => {
  const domains = texts.domains || [
    'stancererrors',
  ];

  delete texts.domains;

  return {
    texts,
    domains,
  };
});
