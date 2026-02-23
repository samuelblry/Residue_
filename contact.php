<?php 
require_once 'includes/db.php'; 
include 'includes/header.php'; 
?>

    <section class="contactContainer">
        <h2 class="titleFormular">Un problème ?</h2>
        <p class="subtitleFormular">Remplissez ce formulaire, on vous répond sous 24H !</p>

        <form id="contactForm" class="formularContainer" novalidate>
            
            <div class="formGroup">
                <label for="firstname">Prénom *</label>
                <input type="text" id="firstname" name="firstname" class="firstNameFormular" autocomplete="given-name" required>
                <span class="errorMsg"></span>
            </div>

            <div class="formGroup">
                <label for="lastname">Nom *</label>
                <input type="text" id="lastname" name="lastname" class="lastNameFormular" autocomplete="family-name" required>
                <span class="errorMsg"></span>
            </div>

            <div class="formGroup">
                <label for="birthdate">Date de naissance *</label>
                <span id="dateHelp" class="inputHelpBirth">Format : JJ/MM/AAAA</span>
                <input type="text" id="birthdate" name="birthdate" class="dateOfBirthFormular" aria-describedby="dateHelp" placeholder="JJ/MM/AAAA" autocomplete="bday" required>
                <span class="errorMsg"></span>
            </div>

            <fieldset class="contactFieldset">
                <legend>Vos coordonnées</legend>
                
                <div class="formGroup">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="emailFormular" autocomplete="email" required>
                    <span class="errorMsg"></span>
                </div>

                <div class="formGroup">
                    <label for="phone">Téléphone *</label>
                    <span id="phoneHelp" class="phoneHelp">Format : 06 12 34 56 78</span>
                    <input type="tel" id="phone" name="phone" class="phoneFormular" aria-describedby="phoneHelp" autocomplete="tel" required>
                    <span class="errorMsg"></span>
                </div>
            </fieldset>

            <fieldset class="timeFormular">
                <legend>Plage horaire préférée *</legend>
                
                <div class="radioGroup">
                    <div>
                        <input type="radio" id="morning" name="time" value="morning">
                        <label for="morning">Matin</label>
                    </div>
                    <div>
                        <input type="radio" id="afternoon" name="time" value="afternoon">
                        <label for="afternoon">Après-midi</label>
                    </div>
                    <div>
                        <input type="radio" id="evening" name="time" value="evening">
                        <label for="evening">Soirée</label>
                    </div>
                </div>
                <span class="errorMsg"></span>
            </fieldset>

            <div class="formGroup">
                <label for="message">Votre message *</label>
                <textarea id="message" name="message" class="messageFormular" rows="5" required></textarea>
                <span class="errorMsg"></span>
            </div>

            <button type="submit" class="sendBtnFormular">Soumettre</button>
        </form>
    </section>

    <div id="successPopup" class="successPopup isHidden" role="dialog" aria-modal="true">
        <div class="popupContent">
            <h2 id="popupTitle">Message soumis !</h2>
            <p>Merci, l'équipe <strong>RESIDUE_</strong>a bien reçu votre demande.</p>
            <button id="closePopupBtn" class="closePopupBtn">Fermer</button>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>