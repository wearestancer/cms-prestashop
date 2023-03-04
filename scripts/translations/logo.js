const terms = [
  {
    en: 'No displayed logo',
    fr: 'Aucun logo affiché',
  },
  {
    en: 'Prefixed with the Stancer logo',
    fr: 'Préfixé par le logo Stancer',
  },
  {
    en: 'Stancer logo with text',
    fr: 'Logo Stancer avec texte',
  },
  {
    en: 'Stancer logo without text',
    fr: 'Logo Stancer sans texte',
  },
  {
    en: 'Suffixed by the Stancer logo',
    fr: 'Suffixé par le logo Stancer',
  },
  {
    en: 'Supported schemes',
    fr: 'Réseau supporté',
  },
  {
    en: 'Visa and Mastercard logos',
    fr: 'Logo Visa et Mastercard',
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: [
      'logo',
    ],
  };
});
