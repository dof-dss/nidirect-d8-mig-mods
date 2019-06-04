module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Landing Page content type exists': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Landing page');
    }
};
