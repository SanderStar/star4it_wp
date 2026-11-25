# Ruimte Reservering WordPress Plugin

Deze plugin biedt een compleet systeem voor het beheren van ruimtes, personen en reserveringen, inclusief iCal-export en conflict-detectie.

## Functies
- Beheer ruimtes, personen en reserveringen via custom admin pagina's
- Telefoonnummer bij personen
- iCal export via `/ruimte-reservering.ics`
- Frontend reserveringsformulier (shortcode `[ruimte_reservering_form]`)
- Conflict-detectie bij reserveringen

## Installatie
1. Upload de map `ruimte-reservering` als zip via het WordPress admin plugin scherm.
2. Activeer de plugin.
3. Gebruik het menu "Ruimte Reservering" in de admin voor beheer.
4. Voeg het reserveringsformulier toe aan een pagina met de shortcode `[ruimte_reservering_form]`.

## iCal Export
De iCal-feed is beschikbaar op: `https://jouwdomein.nl/ruimte-reservering.ics`

Reserveringstijden worden geïnterpreteerd in de tijdzone van WordPress (Instellingen > Algemeen) en in UTC geëxporteerd.

Elke reservering kan een optionele waarde voor **Agenda** krijgen. Filter de iCal-feed op een exacte Agenda-waarde met de `agenda`-parameter, bijvoorbeeld:

`https://jouwdomein.nl/ruimte-reservering.ics?agenda=Team%20A`

Zonder deze parameter bevat de feed reserveringen uit alle agenda's.

## Ondersteuning
Voor vragen of problemen, neem contact op met de ontwikkelaar.
