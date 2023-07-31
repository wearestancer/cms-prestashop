const terms = {
  api_mode: [
    {
      en: 'https://www.stancer.com/documentation/api/#test-cards',
      fr: 'https://www.stancer.com/documentation/api/#test-cards',
    },
    {
      en: 'Check the documentation to find',
      fr: 'Regardez la documentation pour trouver',
    },
    {
      en: 'In test mode, no payment will be sent to a bank, only test cards can be used.',
      fr: 'En mode test, aucun paiement ne sera envoyé à une banque, seules les cartes de test sont utilisables.',
    },
    {
      en: 'test cards',
      fr: 'des cartes de test',
    },
  ],
  auth_limit: [
    {
      en: 'Leave blank if you do not wish to authenticate payments, at zero all payments will be authenticated.',
      fr: 'Laissez vide si vous ne souhaitez pas faire de paiement authentifié, si vous le placez à zéro tous les paiements seront authentifiés.',
    },
    {
      en: 'Minimum amount to trigger an authenticated payment (3DS, Verified by Visa, Mastercard Secure Code...)',
      fr: `Montant minimum pour le déclenchement d'un paiement authentifié (3DS, "Verified by Visa", "Mastercard Secure Code"...)`,
    },
  ],
  infos: [
    {
      en: 'For security reasons, you must enable SSL to use this module.',
      fr: 'Pour des raisons de sécurité, vous devez activer SSL pour utiliser ce module.',
    },
    {
      en: 'link',
      fr: 'ce lien',
    },
    {
      en: 'Open preferences to activate SSL',
      fr: 'Ouvrir les préférences pour activer SSL',
    },
    {
      en: 'This can be done in general preferences available in the left menu or by following this',
      fr: 'Cela peut être fait dans les préférences générales disponibles dans le menu de gauche ou en suivant',
    },
    {
      en: 'This module allows you to accept secure payments by card.',
      fr: "Ce module vous permet d'accepter les paiements par carte de façon simple et sécurisée.",
    },
  ],
  keys: [
    {
      en: 'mandatory in live mode',
      fr: 'obligatoire uniquement en mode production',
    },
    {
      en: 'Starts with "%s"',
      fr: 'Commence avec "%s"',
    },
  ],
  keys_settings: [
    {
      en: 'You can create and recover your API keys on',
      fr: 'Vous pouvez créer et récupérer vos clés API sur',
    },
  ],
  payment_description: [
    {
      en: 'Will be used as description for every payment made.',
      fr: 'Sera utilisé comme description pour chaque paiement.',
    },
    {
      en: 'You may use simple variables, click here to see the list.',
      fr: 'Vous pouvez utiliser des variables, cliquez ici pour avoir la liste.',
    },
  ],
  option: [
    {
      en: "You will be redirected to our partner's portal to make the payment.",
      fr: 'Vous allez être redirigé vers le portail de notre partenaire de paiement.',
    },
  ],
};

module.exports = Object.entries(terms).map(([domain, trads]) => {
  return trads.map((texts) => {
    return {
      domains: [
        domain,
      ],
      texts,
    };
  });
}).flat();
