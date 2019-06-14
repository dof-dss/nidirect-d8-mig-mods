module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-files'],

    'Test whether files have been migrated': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/content/files')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .to.be.present;
    },

    'Test whether any image files have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/files?filename=&filemime=image%2Fjpeg&status=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('image/jpeg');
    },

    'Test whether any PDF files have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/files?filename=&filemime=application%2Fpdf&status=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('application/pdf');
    },

    'Test whether any MS Word files have been migrated': browser => {

        browser
            .drupalRelativeURL('/admin/content/files?filename=&filemime=application%2Fmsword&status=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('application/msword');
    },

    'Test for specific image file': browser => {

        browser
            .drupalRelativeURL('/admin/content/files?filename=%22Schooner+Result+after+completion%22&filemime=image%2Fjpeg&status=All')
            .expect.element('table > tbody > tr > td:nth-child(2)')
            .text.to.contain('image/jpeg');
    }
};
