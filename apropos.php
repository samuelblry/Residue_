<?php 
require_once 'includes/db.php'; 
include 'includes/header.php'; 

// Fetch 4 random articles for the suggestions section
$suggestionsResult = $mysqli->query("
    SELECT a.id, a.name, a.price, 
           (SELECT url FROM image WHERE article_id = a.id AND is_main = 1 LIMIT 1) as main_image
    FROM article a 
    ORDER BY RAND() 
    LIMIT 4
");

$suggestions = [];
if ($suggestionsResult) {
    while($row = $suggestionsResult->fetch_assoc()) {
        $suggestions[] = $row;
    }
}
?>

    <header class="heroSectionAPropos">
        <div class="heroBgContainerAPropos">
            <img src="./img/background/fondApropos.png" class="heroImgAPropos" alt="Image de fond de la section">
        </div>
        <div class="heroContentAPropos">
            <h1 class="heroTitleAPropos">Qui sommes nous ?</h1>
            <p class="heroSubtitleAPropos">no waste, just taste.</p>
        </div>
    </header>

    <section class="presentationBrandAPropos">
        <h2 class="titlePresentationAPropos">RESIDUE_ 2024</h2>
        <h3 class="subtitlePresentationAPropos">BASED IN PARIS — LISBONNE.</h3>
        <p class="textPresentationAPropos">
            LOREM IPSUM DOLOR SIT AMET, CONSECTETUR ADIPISCING ELIT. SED VITENAM AT MONGUE ALIQUAM IN EST LIPIT. URBUM ONON ACCUEIL POSTEARA SIMUL ALIQUAM LACUS. VESTIBULUM FERMENTUM NULLA TEMPOR UTRSUM ULTRICIES CONDIMENTUM ENIM PORTA MAURIS NON MALESUADA NEC, QUATISAN SED LUCTUS EUL MAECENAS POLLETUM JUSTO AT TELLUS ORNARE LUCTUS. NULLAM EFFICITUR NEQUE ORCISSIM QUAM AUCTOR MATTIS. ALIQUAM NUNC MAGNA, ULLAMCORPER VOULTARI SODALES ID, TRISTIQUE ET DUI. CRAS HENDRERIT DUIS CONG FONTNAM DUIS QUISQUE EFFIC IACTIS. INTEGER PRETIUM NISL UTL DERO POSILTRE PULVINAR DONEC EFFICITUR MASSA SED LOREM COMMODO, TUCATEA HASSELLUS IPSUM ULLAMCORPER MAURIS CAMQUE SOLLUCITUDIN FACINIA IN CONGUE NIBH ET JUSTO SED ES AUCTOR MOLESTIE NO UTI URNA MAURIS EU QUAM VITAE MAURIS FRINGILLA GRAVIDA.<br>
            NULLA IN DIAM VITAE EST NULLAM QUIS VOLESTIE PURUS. NEQUEO VIVERNAQUE VIVAMUS EGET DIAM ANTE A MAURIS IN DICT LACINIA PROIN AUGEA ODELE O ULI VOLUSTIE POSUERE EU AC AUGUE. CURS LLLAMOORPER VULPUTATE BLANDIT SED. COMMODO IN DIAM UM. ETIAM  TRISTSI NIBH PRETIUM DI SUSPEND SED SUSCIP T RESLIS NON LUCTUS TINC DUNT. DUIS CONSECTETUR ELEIFEND EGESTAS. MORBI PELLENTESQUE METUS VEL VOLUTPAT AUCTOR PURUS NUNC VEHICULA DIAM, SED ELEMENTUM NUNC ORCI IN NISI PHASELLUS EGET AUGUE.<br>
            VIVAMUS SOLLICITUDIN SODALES CURSUS EGESTAS. PELLENTESQUE BIBENDUM RUTRUM VEHICULA. MAECENAS SCELERISQUE POSUERE LIBERO, EU MALESUADA MAGNA CONGUE EFFICITUR. PRAESENT ULTRICES GRAVIDA ORCI AT MOLESTIE QUAM PHARETRA AT VESTIBULUM UT LAOREET ANTE. SED CONVALLIS TELLUS, SAPINC EST VEL SUI AC NULLA. VIVAMUS IN NIBH VELIT LAOREET PLATSI, AT COMMODO LIGULA. FAUGIAT AUCTOR AUCIBUS TINCUNT EUL PULLUN IS DUS LACINIA NUL EUM ENIM IN URNA ETIAN DA ING ET SAPIEN ORNARET ORARE LACINIA IN CONVALLIS DIAM PELLENTESQUE SOLLICITUDIN VEL EX SAPIEN. NULLA EU EFFING DIAM UT GRAVIDA. LUCTUS LACUS NISL CONSECTETUR EST. RHONCUS SAPIEN MALISUTA ET LIBERA. UI ESUE MALESUADA. CONVALLIS CUS MAXIMUS LIGULA VENENATIS T LLUM URGUESTELLUS JUSTO AC BITCUS IN VOLES ET OSTR AUGUE MALESUADA VEL LACINIA IPSUM FINIBUS.<br>
            CONSEQUATUL LA NECH BULUM QUIS ORSARI SEIS, LUCTUS VEL URNA NAM LAT ARCU VEL MAURIS LACINIA VEHICULA ET CONDIMENTUM VELIT MORBI EX EX PLACER  VENGUE FAUCIBUS ARCU MAURIS, ET FINIBUS ARCUS CTI, VIVIL SED CONDIMENTUM PHARETRA MAGNA. NON INTERDUM E AM DAPIBUS NON VESTIBULUM INTO MASSA MAULACUS PLACERAT INQUE SI VESTRA, LUNA MI ULTRICES, ELEMENTUM AC LECTUS IN PULVAN  EIQUE TEN PUSU ESCC NECT P ART IN S ODALES JUSTO VITAE TE STIQUE. UT MAECYUS GRAVIDA METUS SIT AMET VULPUTATE. PHASELLUS HIT IMPERDIET LOREM. CURABITUR AUCTOR A UX MAMIMIM IN SUSCIPIT VELITSAG TTIS NON. NULLA IACULIS ERE T CA CU D T TEL EUM ICULR SEM A, LIQU AM U N QU N H AC LLA VIVERRA QUAM IN ULLAMCORPER PHA LIG VES E MULUM IPSUM S M AVE F   MATT EST V E P RIL L. URNA LECTUS COMMODO LIBERO NON LLI TR DI SI LIBERO ET UI FEUGIAT TELLUS. SED VULPUTATE FEUGIAT PHARETRA.
        </p>
        <p class="sloganAPropos">NO WASTE, JUST TASTE.</p>
    </section>

    <section class="timelineAPropos">
        <h2 class="titleTimeline">TIMELINE</h2>
        <h3 class="subtitleTimeline">CREDIT : FAKE VOCABULAIRE</h3>
        
        <div class="timelineImages">
            <div class="timelineItem">
                <img src="./img/2026.png" alt="Collection 2026">
                <div class="yearOverlay">2026</div>
            </div>
            <div class="timelineItem">
                <img src="./img/2025.png" alt="Collection 2025">
                <div class="yearOverlay">2025</div>
            </div>
            <div class="timelineItem">
                <img src="./img/2024.png" alt="Collection 2024">
                <div class="yearOverlay">2024</div>
            </div>
        </div>
        <p class="sloganAPropos sloganTimeline">NO WASTE, JUST TASTE.</p>
    </section>

    <div class="separationCategoryArticle" style="margin-bottom: 3rem;"></div>

    <section class="suggestionsAPropos">
        <h2 class="titleSuggestions">SUGGESTIONS</h2>
        <div class="suggestionsGrid">
            <?php foreach($suggestions as $article): ?>
            <div class="suggestionCard">
                <div class="suggestionImgWrapper">
                    <?php if(!empty($article['main_image'])): ?>
                        <img src="<?php echo htmlspecialchars($article['main_image']); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>">
                    <?php else: ?>
                        <div class="placeholderImg"></div>
                    <?php endif; ?>
                </div>
                <div class="suggestionInfo">
                    <h4 class="suggestionName"><?php echo htmlspecialchars($article['name']); ?></h4>
                    <span class="suggestionPrice"><?php echo number_format($article['price'], 2); ?> EUR</span>
                    <div class="suggestionColors">
                        <span class="colorDot" style="background-color: #dc2626;"></span>
                        <span class="colorDot" style="background-color: #3b82f6;"></span>
                        <span class="colorDot" style="background-color: #d1d5db;"></span>
                        <span class="colorDot" style="background-color: #1c1917;"></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>