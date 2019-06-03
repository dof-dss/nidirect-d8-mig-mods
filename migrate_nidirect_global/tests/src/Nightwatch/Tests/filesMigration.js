module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether files have been migrated': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/content/files')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .to.be.present;
    }
};
