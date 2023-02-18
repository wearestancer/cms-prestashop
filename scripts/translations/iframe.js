const terms = [
  {
    en: 'Please confirm terms and conditions before pursuing.',
    fr: "Merci d'accepter les conditions avant de poursuivre.",
  },
];

module.exports = terms.map((texts) => {
  return {
    texts,
    domains: [
      'iframe',
    ],
  };
});
