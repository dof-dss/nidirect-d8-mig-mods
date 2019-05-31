module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Article content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/structure/types')
            .expect.element('table > tbody > tr:nth-child(2) > td.menu-label')
            .text.to.equal('Article');

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-banner-image').to.be.present;
    }
};
