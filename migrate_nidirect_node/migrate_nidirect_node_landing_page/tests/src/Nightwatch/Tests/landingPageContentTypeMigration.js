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
    },

    'Test whether Landing Page nodes exist': browser => {

        // See if we have any landing_page nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=landing_page')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Landing page');

        // Now test a random sample of actual nodes.


    }
};
