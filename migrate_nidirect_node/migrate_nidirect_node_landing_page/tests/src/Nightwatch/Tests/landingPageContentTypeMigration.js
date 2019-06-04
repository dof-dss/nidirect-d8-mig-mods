module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Landing Page content type exists': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Landing page');
    },

    'Test whether Landing Page fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-banner-image').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-banner-image-overlay').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#body').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-enable-title').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-summary').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-teaser').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-subtheme').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-top-level-theme').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/landing_page/fields')
            .expect.element('#field-summary').to.be.present;
    }
};
