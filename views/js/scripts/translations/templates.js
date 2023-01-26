/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const terms = [
  {
    en: 'Card',
    fr: 'Carte',
  },
  {
    en: 'You will be redirected to our partner\'s portal to make the payment.',
    fr: 'Vous allez être redirigé vers le portail de notre partenaire de paiement.',
  },
  {
    en: 'Pay with your %s finishing with %s',
    fr: 'Payer avec votre %s finissant avec %s',
  },
  {
    en: 'Pay by card',
    fr: 'Payer par carte',
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: [
      'core',
      'option',
      'payment',
      'validation',
    ],
  };
});
