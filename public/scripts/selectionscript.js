import CSSVARS from './cssGlobals.js';
import videoList from './videolist.js';

/**constanten */

const THEMES = [
    'Mobiliteit',
    'Energietransitie',
    'Klimaatverandering',
    'Watermanagement',
    'Grondstoffen',
    'Voedselinnovatie',
    'Biodiversiteit',
    'InternetofThings'
];

const LEVELS = ['1', '2', '3'];
const MAX_SELECTION = 8;
const MIN_SELECTION = 2;
const startQuizButton = document.getElementById('StartQuizknop');
const startContentContainer = document.querySelector('.start-knop-content');
const downloadButton = document.getElementById('Downloadknop');


let selectedCheckboxes = [];
let hoverTimeout;

const beschikbareVideos = videoList;

const {
    displayNone,
    loaderHidden,
    extra,
    checkboxAllChecked,
    checkboxSomeChecked,
    checkboxNoneChecked
    
} = CSSVARS;


/**functies */

//disabled klasse toggle functie 

function updateDisabledClass(){
    const rendered = (startContentContainer && startQuizButton);
    if (!rendered) return

    if (startQuizButton.disabled) {
        startContentContainer.classList.add('has-disabled')
    } else {
         startContentContainer.remove('has-disabled', 'show-tooltip');
    }

}

const getLabelId = (theme) => {
    const themeData = beschikbareVideos[theme];

    if (!themeData) return null;

    const levels = Object.keys(themeData);

    if (!levels.length) return null;

    const levelString = levels.join("");

    return `${theme}${levelString}`;
    
}

function handlePageLoad() {
    const loadingScreen = document.getElementById('loadingScreen');

    if (!loadingScreen) {
        console.warn('loadingscreen with spinner was not found');
        return;
    }

    loadingScreen.classList.add(loaderHidden);

    setTimeout(() => {
        loadingScreen.classList.add(displayNone);
        
    }, 500);
}

function addHoverListeners() {
    const disabledCheckboxes = document.querySelectorAll('.themes-form__checkbox--disabled');
    disabledCheckboxes.forEach(checkbox => {
        const label = checkbox.nextElementSibling;

        /**next sibling is elementen op het zelfde niveau  */

        checkbox.addEventListener('mouseenter', () => scheduleHoverMessage('Nog geen FutureFilm beschikbaar', checkbox));
        label.addEventListener('mouseenter', () => scheduleHoverMessage('Nog geen FutureFilm beschikbaar', label));

        checkbox.addEventListener('mouseleave', cancelHoverMessage);
        label.addEventListener('mouseleave', cancelHoverMessage);
    });
}

function scheduleHoverMessage(message, element) {
    clearTimeout(hoverTimeout);
    hoverTimeout = setTimeout(() => showHoverMessage(message, element), 300); // Voeg een vertraging van 300 ms toe
}

function cancelHoverMessage() {
    clearTimeout(hoverTimeout);
    hideHoverMessage();
}

function addLevelToggleHoverListeners() {
    const levelMessages = {
        '1': 'Niveau Basisonderwijs',
        '2': 'Niveau VO onderbouw',
        '3': 'Niveau VO bovenbouw'
    };

    const levelToggles = document.querySelectorAll('.themes-form__level-toggle');
    levelToggles.forEach(toggle => {
        const level = toggle.dataset.level;

        toggle.addEventListener('mouseenter', () => scheduleHoverMessage(levelMessages[level], toggle));
        toggle.nextElementSibling.addEventListener('mouseenter', () => scheduleHoverMessage(levelMessages[level], toggle.nextElementSibling));

        toggle.addEventListener('mouseleave', cancelHoverMessage);
        toggle.nextElementSibling.addEventListener('mouseleave', cancelHoverMessage);
    });
}

function showHoverMessage(message, element) {
    if (element.classList.contains('themes-form__checkbox')){
        return;
    }
    let hoverMessageElement = document.querySelector('.hover-message');
    if (!hoverMessageElement) {
        hoverMessageElement = document.createElement('div');
        hoverMessageElement.className = 'hover-message';
        document.body.appendChild(hoverMessageElement);
    }
    hoverMessageElement.textContent = message;
    hoverMessageElement.style.display = 'block';
    hoverMessageElement.style.opacity = '1';

    const rect = element.getBoundingClientRect();
    hoverMessageElement.style.left = `${rect.left + window.scrollX}px`;
    hoverMessageElement.style.top = `${rect.top + window.scrollY - 30}px`; // 30 pixels boven het element
}

function hideHoverMessage() {
    const hoverMessageElement = document.querySelector('.hover-message');
    if (hoverMessageElement) {
        hoverMessageElement.style.opacity = '0';
        setTimeout(() => {
            hoverMessageElement.style.display = 'none';
        }, 300); // Wacht tot de overgang voltooid is
    }
}

function handleCheckboxChange(event) {
    const updateSelectorIcon = (selected, number) => {

        const selectorRow = document.getElementById('level-selector-row');
        if (!selectorRow) return;

        const iconLabel = selectorRow.querySelector('#levelSelectorCheckBoxLabel');

        if (!iconLabel) return;

        console.log(iconLabel.classList);

        if (selected && number < MAX_SELECTION) {

            iconLabel.classList.remove(checkboxAllChecked);
            iconLabel.classList.remove(checkboxNoneChecked);

            if (!iconLabel.classList.contains(checkboxSomeChecked)){
                iconLabel.classList.add(checkboxSomeChecked);
            }
            return;

        } else if (selected && number === MAX_SELECTION) {
            iconLabel.classList.remove(checkboxSomeChecked);
            iconLabel.classList.remove(checkboxNoneChecked);

            if (!iconLabel.classList.contains(checkboxAllChecked)){
                iconLabel.classList.add(checkboxAllChecked);
            }
            return;
           
        } else if (!selected) {
            iconLabel.classList.remove(checkboxSomeChecked);
            iconLabel.classList.remove(checkboxAllChecked);

             if (!iconLabel.classList.contains(checkboxNoneChecked)){
                iconLabel.classList.add(checkboxNoneChecked);
            }
            return;

        }
        return;
    }


    const { id, checked } = event.target;

    if (checked) {
        if (!selectedCheckboxes.includes(id)){
            selectedCheckboxes.push(id);
            /**geen dubbele id toelaten anders lijkt het alsof je over de max selectie gaat terwijl dezelfde id er twee keer inzit */
        }




        if (selectedCheckboxes.length > MAX_SELECTION) {
            const toUncheck = selectedCheckboxes.shift();
            document.getElementById(toUncheck).checked = false;
        } 
    } else {
        selectedCheckboxes = selectedCheckboxes.filter(selectedId => selectedId !== id);
    }

    updateSelectorIcon(Boolean(selectedCheckboxes.length > 0), Number(selectedCheckboxes.length));

    if (selectedCheckboxes.length < MIN_SELECTION){
        startQuizButton.disabled = true;

        if (selectedCheckboxes.length === 1){
               const selectedId = selectedCheckboxes[0];
               if (selectedId){
                const selectedCheckbox = document.querySelector(`input[type="checkbox"]#${selectedId}`);
                startContentContainer.setAttribute(
                    "data-info-disabled-tip", 
                    `Selecteer naast ${selectedCheckbox.name} nog 1 andere categorie`);

               } else {
                    startContentContainer.setAttribute(
                    "data-info-disabled-tip", 
                    `Selecteer nog 1 categorie naar keuze erbij om de JukeBox te starten`);
               }
        } else if (!selectedCheckboxes.length) {
            startContentContainer.setAttribute(
            "data-info-disabled-tip", 
            'Selecteer twee categorieën om de JukeBox te starten');
        }
    } else {
        startQuizButton.disabled = false;
        startContentContainer.setAttribute(
            "data-info-disabled-tip", 
            'Selecteer twee categorieën om de JukeBox te starten');
    }
    
}


function showWarningMessage(message) {
    const messageElement = document.createElement('div');
    messageElement.className = 'warning-message';
    messageElement.textContent = message;
    document.body.appendChild(messageElement);

    setTimeout(() => {
        messageElement.remove();
    }, 5000); // Verwijder de melding na 5000 milliseconden (5 seconden)
}

function isLevelAvailable(level) {
        return THEMES.every(theme => beschikbareVideos[theme] && beschikbareVideos[theme][level] && beschikbareVideos[theme][level].length > 0);
}

const getSelectorLabelId = () => {
    let parts = [];
    let levels = "";

    LEVELS.forEach((level) => {
        if (isLevelAvailable(level)) {
            parts.push(`niv${level}`)
        }
    })

    if (parts.length) levels = parts.join("");

    return { 
        'id' : `levelSelector${levels}`,
        'levels' : levels
    };
}


function submitSelection(event) {
    if (selectedCheckboxes.length === 0) {
        showWarningMessage('Selecteer ten minste 1 checkbox om de quiz te starten.');
        return; // Stop de functie als er geen selecties zijn gemaakt
    }

    const selectedThemes = {};
    THEMES.forEach((theme) => {
        selectedThemes[theme] = [];
        LEVELS.forEach((level) => {
            const checkboxId = theme + level;
            if (selectedCheckboxes.includes(checkboxId)) {
                selectedThemes[theme].push(level);
            }
            // selectedThemes structuur = {'Mobiliteit':['1','2']}
        });
    });

    // Filter out themes that do not have any levels selected
    Object.keys(selectedThemes).forEach(theme => {
        if (selectedThemes[theme].length === 0) {
            delete selectedThemes[theme];
        }
    });

    if (Object.keys(selectedThemes).length === 1) {
        // Vind een willekeurig thema om toe te voegen dat niet het al geselecteerde thema is
        const beschikbareThemas = THEMES.filter(thema => !selectedThemes.hasOwnProperty(thema));

        /**hasownproperty controleer de eigen waarde dus de sleutels van selectedThemes, maar niet de nested */

        const randomThema = beschikbareThemas[Math.floor(Math.random() * beschikbareThemas.length)];
        // Voeg dit thema toe met alleen niveau '1'
        selectedThemes[randomThema] = ['1'];
        /** structuur  'mobiliteit': ['1','2'], 'biodiversiteit': ['1'] */
    }

    opslaanGeselecteerdeVideos(selectedThemes); // Aanroepen van de nieuwe functie.
    window.location.href = './videoplayer.html';

    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

function opslaanGeselecteerdeVideos(selectedThemes) {
    const eindLijst = [];
    const themaLijsten = {}; // Tijdelijke opslag voor video's per thema en niveau
    let niveauIndexen = {};

    // Initialiseer niveauIndexen voor elk thema op een willekeurige startindex
    Object.keys(selectedThemes).forEach(theme => {
        if (selectedThemes[theme].length > 0) {
            // Willekeurige startindex binnen de beschikbare niveaus van het thema
            niveauIndexen[theme] = Math.floor(Math.random() * selectedThemes[theme].length);

            /**bij 'Mobiliteit' : ['1', '2'] kies ie dus een random index */
        }
    });

    // Stap 1: Verzamel alle beschikbare video's per thema en niveau
    Object.keys(selectedThemes).forEach(theme => {
        themaLijsten[theme] = {};
        selectedThemes[theme].forEach(level => {
            if (beschikbareVideos[theme] && beschikbareVideos[theme][level]) {
                if (!themaLijsten[theme][level]) {
                    themaLijsten[theme][level] = [];
                }
                themaLijsten[theme][level] = themaLijsten[theme][level].concat(beschikbareVideos[theme][level]);
            }
        });
    });

    // Stap 2: Selecteer 8 willekeurige video's afwisselend per thema en niveau
    const themaKeys = Object.keys(themaLijsten).filter(theme => Object.keys(themaLijsten[theme]).some(level => themaLijsten[theme][level].length > 0));
    let themaIndex = 0; // Startpunt voor thema-selectie

    while (eindLijst.length < 8 && themaKeys.length > 0) {
        const thema = themaKeys[themaIndex % themaKeys.length];
        const niveauKeys = Object.keys(themaLijsten[thema]);
        let niveauIndex = niveauKeys.length > 1 ? (niveauIndexen[thema] || 0) : 0; // Gebruik de opgeslagen index voor dit thema
        const niveau = niveauKeys[niveauIndex];
        const videos = themaLijsten[thema][niveau];

        if (videos.length > 0) {
            const videoIndex = Math.floor(Math.random() * videos.length);
            eindLijst.push(videos.splice(videoIndex, 1)[0]);
        }

        // Update de niveauIndex voor het huidige thema
        if (niveauKeys.length > 1) {
            niveauIndexen[thema] = (niveauIndex + 1) % niveauKeys.length;
        }

        // Verwijder thema als er geen video's meer zijn
        if (videos.length === 0) {
            themaKeys.splice(themaIndex % themaKeys.length, 1);
        } else {
            themaIndex++;
        }
    }
    console.log(eindLijst);
    localStorage.setItem('videoLijst', JSON.stringify(eindLijst)); // Opslaan in localStorage
}



function downloadPDF() {
    var pdfLink = document.createElement('a');
    pdfLink.href = './assets/spelbord_future_films.pdf';
    pdfLink.download = 'spelbord_future_films.pdf';
    pdfLink.target = '_blank';
    document.body.appendChild(pdfLink);
    pdfLink.click();
    document.body.removeChild(pdfLink);
}


/**opbouwen van de pagina met het selectie menu */
const form = document.getElementById('themesForm');
//om rendering te verbeteren
const fragment = document.createDocumentFragment(); 

/* ------------------------------------------------------ */
/* Stap 1: De "Level Selector" rij genereren              */
/* ------------------------------------------------------ */

//main div levelSelector
const levelSelectorRow = document.createElement('div');
levelSelectorRow.className = 'themes-form__row themes-form__row--level-selector'; 
levelSelectorRow.id = 'level-selector-row';

//label for levelselector
const levelSelectorLabel = document.createElement('label');
levelSelectorLabel.className = 'themes-form__label';
levelSelectorLabel.htmlFor = getSelectorLabelId()?.levels ?? 'levelSelector';
levelSelectorLabel.textContent = 'Selector';

const levelSelectorHiddenInput = document.createElement('input');
levelSelectorHiddenInput.type = 'hidden';
levelSelectorHiddenInput.id = `hidden${getSelectorLabelId()?.levels}` ?? 'levelSelector';
levelSelectorHiddenInput.name = 'levelSelectorInput';
levelSelectorHiddenInput.value = getSelectorLabelId()?.levels ?? "";
levelSelectorHiddenInput.className = "d-none";


levelSelectorRow.appendChild(levelSelectorLabel);
levelSelectorRow.appendChild(levelSelectorHiddenInput);


//container voor de controls (checkbox inputs)
const levelTogglesContainer = document.createElement('div');
levelTogglesContainer.className = 'themes-form__controls';
LEVELS.forEach(level => {
    const checkboxId = `niv${level}`;

    const input = document.createElement('input');
    input.type = 'checkbox';
    input.id = checkboxId;
    input.className = 'themes-form__checkbox themes-form__level-toggle';
    input.dataset.level = level;

    // Controleer of alle thema's beschikbaar zijn voor dit niveau
    if (!isLevelAvailable(level)) {
        input.disabled = true;
    }

    const label = document.createElement('label');
    label.htmlFor = checkboxId;
    label.className = 'themes-form__checkbox-label';
    label.textContent = ""; //hier stond level

    if (!input.disabled) {
    levelTogglesContainer.appendChild(input);
    levelTogglesContainer.appendChild(label);};
});

const iconLabelLevelSelector = document.createElement('label');
iconLabelLevelSelector.className = 'themes-form__icon-label checkbox-none-checked extra3 extra2 extra1';
iconLabelLevelSelector.id = 'levelSelectorCheckBoxLabel';

const firstCheckboxLevelSelector = levelTogglesContainer.querySelector('input') ?? null;
if (firstCheckboxLevelSelector) iconLabelLevelSelector.htmlFor = firstCheckboxLevelSelector.id;

levelTogglesContainer.appendChild(iconLabelLevelSelector);

levelSelectorRow.appendChild(levelTogglesContainer);
fragment.appendChild(levelSelectorRow);

/* ------------------------------------------------------ */
/* Stap 2: Alle Thema-rijen genereren                     */
/* ------------------------------------------------------ */

THEMES.forEach((theme) => {
    const themeRow = document.createElement('div');
    themeRow.className = 'themes-form__row';

    //hoofdlabel met naam van het thema
    const label = document.createElement('label');
    label.textContent = theme;
    label.htmlFor = getLabelId(theme) ?? theme;
    themeRow.appendChild(label);

    const labelHiddenInput = document.createElement('input');
    labelHiddenInput.type = 'hidden';
    labelHiddenInput.id =  `hidden${getLabelId()}` ?? theme;
    labelHiddenInput.name = 'labelHiddenThemeInput';
    labelHiddenInput.value = getLabelId() ?? theme;
    labelHiddenInput.className = "d-none";

    themeRow.appendChild(labelHiddenInput);

    const themeControlsContainer = document.createElement('div');
    themeControlsContainer.className = 'themes-form__controls';

    const levelCheckboxContainer = document.createElement('div');
    levelCheckboxContainer.className = 'themes-form__level-checkboxes';
    
    LEVELS.forEach((level) => {
        const checkboxId = `${theme}${level}`;
        const isLevelAvailable = (
            beschikbareVideos[theme] 
                && 
            beschikbareVideos[theme][level]
                &&
            beschikbareVideos[theme][level].length
        )

        if (isLevelAvailable){
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.id = checkboxId;
            input.name = theme;
            input.value = level;
            input.className = 'themes-form__checkbox';

            input.addEventListener('change', handleCheckboxChange);

            const label = document.createElement('label');
            label.htmlFor = checkboxId;
            label.className = 'themes-form__checkbox-label';
            label.textContent = "";
            
            levelCheckboxContainer.appendChild(input);
            levelCheckboxContainer.appendChild(label);
        }      
    });
    // voeg de level checboxes en het icoontje toe aan de controls -- is nog steeds per rij 
    themeControlsContainer.appendChild(levelCheckboxContainer);
    const iconLabel = document.createElement('label');

    //geen selectall dus de eerste
    const firstCheckbox = levelCheckboxContainer.querySelector('.themes-form__checkbox');

    if (firstCheckbox) iconLabel.htmlFor = firstCheckbox.id;

    iconLabel.className = 'themes-form__icon-label';
    themeControlsContainer.appendChild(iconLabel);

    themeRow.appendChild(themeControlsContainer);
    fragment.appendChild(themeRow);
});

form.appendChild(fragment);

    
addHoverListeners(); // Voeg deze toe na de elementen zijn aangemaakt
addLevelToggleHoverListeners();

if (startContentContainer && startQuizButton) {
  const updateDisabledClass = () => {
    if (startQuizButton.disabled) startContentContainer.classList.add('has-disabled');
    else startContentContainer.classList.remove('has-disabled', 'show-tooltip');
  };

  // initial state
  updateDisabledClass();

  // hover in/out → tooltip tonen/verbergen
  startQuizButton.addEventListener('mouseenter', () => {
    if (startQuizButton.disabled) startContentContainer.classList.add('show-tooltip');
  });
  startQuizButton.addEventListener('mouseleave', () => {
    startContentContainer.classList.remove('show-tooltip');
  });

  startQuizButton.addEventListener('click', submitSelection);

  // als je in je app de disabled-property wijzigt, vang dat op

/**
 * dus er vander iets aan disabled -> updateClass uitvoeren 
 */
  const obs = new MutationObserver(updateDisabledClass);
  obs.observe(startQuizButton, { attributes: true, attributeFilter: ['disabled'] });
}



    // Level toggle logica
const levelToggles = document.querySelectorAll('.themes-form__level-toggle');
levelToggles.forEach(toggle => {
    toggle.addEventListener('change', function () {
        const level = this.dataset.level;
        const checkboxes = document.querySelectorAll(`input[type='checkbox'][value='${level}']`);
        checkboxes.forEach(checkbox => {
            if (!checkbox.disabled && checkbox.checked !== this.checked) {

                /**
                 * this.checked verwijst naar dat all level toggle
                 * en de checkboxes naar de thema checkboxes
                 * dus zet je de all level aan dan alle checkbox van dat level aanzetten
                 * ?maar niet als ie al aanstaat, maar dan hoef je niet nog een keer aan te zetten
                 * todo: dus deze check checkbox.checked !== this.checked hierboven
                 */

                
                checkbox.checked = this.checked;
                handleCheckboxChange({ target: checkbox }); // Update selectedCheckboxes array
            }
        });
    });
});


    // Updaten van level toggle checkboxes op checkbox wijzigingen
const allCheckboxes = document.querySelectorAll('input[type="checkbox"][name]');
allCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const level = this.value;
        const levelToggle = document.querySelector(`#niv${level}`);
        if (!this.checked) {
            levelToggle.checked = false;
        } else {
            const allOfLevel = document.querySelectorAll(`input[type='checkbox'][value='${level}']:not(:disabled)`);
            const allChecked = Array.from(allOfLevel).every(cb => cb.checked);
            levelToggle.checked = allChecked;
        }
    });
});

downloadButton.addEventListener('click', downloadPDF);


document.addEventListener('wheel', function (event) {
    if (event.ctrlKey) { event.preventDefault(); }
}, { passive: false });

window.addEventListener('load', handlePageLoad);