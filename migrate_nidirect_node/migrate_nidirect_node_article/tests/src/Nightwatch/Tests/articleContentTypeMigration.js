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

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#body').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#comment').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-enable-toc').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-additional-info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-image').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-photo').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-summary').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-site-topics').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-teaser').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-subtheme').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-top-level-theme').to.be.present;

        browser
            .drupalRelativeURL('/admin/content?type=article')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Article');
    }
};
