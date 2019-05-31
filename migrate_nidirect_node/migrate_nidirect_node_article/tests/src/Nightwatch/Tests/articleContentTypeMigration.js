module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Article content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' })
            .drupalRelativeURL('/admin/structure/types')
            .expect.element('table > tbody > tr:nth-child(2) > td.menu-label')
            .text.to.equal('Article');
    }
};
