module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Metatag tokens exist on Articles': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/article/fields')
            .expect.element('#field-meta-tags').to.be.present;
    },

    'Test whether Metatag global config has been imported': browser => {
        browser
            .drupalRelativeURL('/admin/config/search/metatag')
            .useXpath()
            .expect.element('//td[text()="[node:field-summary]"]').to.be.present;
    }


};
