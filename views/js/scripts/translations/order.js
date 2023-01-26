/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
const terms = [
  {
    en: 'Thanks for your order, %s.',
    fr: 'Merci pour votre commande %s.',
  },
  {
    en: 'You will received a confirmation e-mail shortly.',
    fr: 'Vous allez recevoir un e-mail de confirmation sous peu.',
  },
  {
    en: 'Unfortunately your payment was declined.',
    fr: 'Malheureusement votre paiement a été refusé.',
  },
  {
    en: 'Don\'t panic, no withdrawals will be made.',
    fr: 'Pas de panique, aucun prélèvement ne sera fait.',
  },
  {
    en: 'You may want to redo the same order,',
    fr: 'Pour refaire la commande à l\'identique,',
  },
  {
    en: 'click here',
    fr: 'cliquez ici',
  },
  {
    en: 'Redo the same order',
    fr: 'Refaire la même commande',
  },
  {
    en: 'Order details:',
    fr: 'Détail de votre commande :',
  },
  {
    en: 'Order reference',
    fr: 'Référence de la commande',
  },
  {
    en: 'Your order history',
    fr: 'Votre historique de commande',
  },
  {
    en: 'Status',
    fr: 'État',
  },
  {
    en: '(%d items)',
    fr: '(%d articles)',
  },
  {
    en: '(1 item)',
    fr: '(1 article)',
  },
  {
    en: 'Products',
    fr: 'Produits',
  },
  {
    en: 'Discounts',
    fr: 'Réductions',
  },
  {
    en: 'Shipping',
    fr: 'Frais de transport',
  },
  {
    en: 'Gift wrapping',
    fr: 'Emballage cadeau',
  },
  {
    en: 'Total amount',
    fr: 'Total',
  },
  {
    en: '(tax incl.)',
    fr: 'TTC',
  },
  {
    en: '(tax excl.)',
    fr: 'HT',
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: [
      'payment_return',
    ],
  };
});
