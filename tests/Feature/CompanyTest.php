<?php

test('company pages render in each supported language', function (string $locale, string $headline, string $htmlLanguage) {
    $this->get('/'.$locale)->assertOk()->assertSee($headline)->assertSee('lang="'.$htmlLanguage.'"', false)
        ->assertSee('mailto:hello@artabumisentraindustri.com', false);
})->with([
    ['id', 'Mengolah potensi.', 'id'],
    ['en', 'Developing potential.', 'en'],
    ['zh', '发掘潜力。', 'zh-Hans'],
]);

test('archive articles have localized content and language-preserving links', function (string $locale) {
    foreach (range(0, 2) as $article) {
        $this->get('/'.$locale.'/news/'.$article)->assertOk()
            ->assertSee('/en/news/'.$article, false)
            ->assertSee('/zh/news/'.$article, false);
    }
})->with(['id', 'en', 'zh']);

test('unsupported language and missing articles return not found', function () {
    $this->get('/fr')->assertNotFound();
    $this->get('/id/news/99')->assertNotFound();
});
