const terms = [
  {
    en: 'This payment method is actualy unavailable.',
    fr: 'Ce moyen de paiement est actuellement indisponible.',
  },
  {
    en: 'Please contact us to unlock this situation.',
    fr: 'Contactez nous pour débloquer la situation.',
  },
  {
    en: 'Configuration error, unknown mode "%s".',
    fr: 'Erreur de configuration, le mode "%s" est inconnu.',
  },
  {
    en: 'Impossible to connect to the payment platform.',
    fr: 'Impossible de se connecter à la plateforme de paiement.',
  },
  {
    en: 'The payment platform is actualy unavailable.',
    fr: 'La plateforme de paiement est actuellement indisponible.',
  },
  {
    en: 'Please wait a minute and try again.',
    fr: "Merci de patienter quelques instant avant de recommencer l'opération.",
  },
  {
    en: 'This error may be temporary, please try again.',
    fr: "Cette erreur devrait être temporaire, merci de renouveler l'opération ultérieurement.",
  },
  {
    en: 'In cas this message persists, please contact the store to resolve this issue as soon as possible.',
    fr: 'Si ce message persiste, veuillez contacter la boutique pour résoudre ce souci le plus vite possible.',
  },
  {
    en: 'No payment found for this cart.',
    fr: 'Aucun paiement trouvé pour ce panier.',
  },
  {
    en: 'The payment attempt failed.',
    fr: 'La tentative de paiement a échouée.',
  },
  {
    en: 'Your card has not been charged.',
    fr: "Votre carte n'a pas été débitée.",
  },
  {
    en: 'An unknown error occured during connexion to the payment plateform.',
    fr: "Une erreur inconnue s'est produite lors de la connexion à la plateforme de paiement.",
  },
  {
    en: 'The payment platform is actualy unreacheable.',
    fr: 'La plateforme de paiement est actuellement inaccessible.',
  },
  {
    en: 'Back',
    fr: 'Retour',
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: [
      'stancererrors',
      'error',
    ],
  };
});
