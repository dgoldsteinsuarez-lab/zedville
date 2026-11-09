<?php

// Die Betrugsfälle des Intermediate (App\Services\FraudService): der Anruf auf dem Bildschirm, die gefälschte
// E-Mail der Abteilung für Finanzbildung, die falsche Seite, was passiert ist und die Hilfe der Bank. Begriffe:
// Phishing, Vishing, Call-ID-Spoofing, Messenger-Betrug („Hallo Mama“), Finanzagent; Stoppen, Hinterfragen, Schützen.
return [
    // der eingehende Anruf (partials/fraud-call)
    'call_incoming' => 'Eingehender Anruf',
    'call_answer' => 'Annehmen',
    'call_decline' => 'Ablehnen',
    'call_choose' => 'Was machst du?',
    'call_close' => 'Schließen',
    'callers' => [
        'bank_code' => 'Universal Bank · Sicherheit',
        'safe_account' => 'Universal Bank · Sicherheit',
        'tech_support' => 'Technischer Support',
        'family' => 'Unbekannte Nummer',
    ],
    'lines' => [
        'bank_code' => ['Hallo, hier ist die Sicherheitsabteilung der Universal Bank. Wir haben eine Zahlung von :charge mit Ihrer Karte gestoppt.', 'Um sie zu stornieren, lesen Sie mir den Code vor, den wir Ihnen gerade per SMS geschickt haben. Schnell, sonst geht die Zahlung durch.'],
        'safe_account' => ['Hallo, hier ist die Sicherheitsabteilung der Universal Bank. Gerade versucht jemand, in Ihr Konto zu kommen.', 'Um Ihr Geld zu schützen, überweisen Sie es auf dieses sichere Konto: ZV00 1234 5678. Tun Sie es sofort, bevor Ihres leer geräumt wird.'],
        'tech_support' => ['Hallo, hier ist der technische Support. Ihr Computer hat einen Virus, der uns Ihre Bankdaten schickt.', 'Installieren Sie das Programm, das ich Ihnen schicke, dann entfernen wir ihn. Es kostet :repair, per Karte.'],
        'family' => ['Hallo, ich bin\'s, dein Cousin! Ich habe mein Handy verloren, das ist meine neue Nummer.', 'Ich muss dringend etwas bezahlen und meine Karte geht nicht. Kannst du mir Geld überweisen? Ich zahle es dir morgen zurück.'],
    ],
    'answers' => [
        'hang_up_call_bank' => 'Ich lege auf und rufe selbst die Bank an, unter ihrer offiziellen Nummer',
        'give_code' => 'Ich lese den Code vor, damit die Zahlung storniert wird',
        'ask_then_give' => 'Ich frage nach dem Namen und, wenn er ihn sagt, gebe ich den Code',
        'hang_up_safe' => 'Ich lege auf: Meine Bank würde mich nie bitten, mein Geld zu verschieben',
        'move_all' => 'Ich überweise mein ganzes Geld auf das sichere Konto',
        'move_half' => 'Ich überweise nur die Hälfte, sicher ist sicher',
        'hang_up_tech' => 'Ich lege auf: Ich habe niemanden um Hilfe gebeten',
        'install' => 'Ich installiere das Programm, damit sie den Virus entfernen',
        'pay_fix' => 'Ich bezahle die Reparatur mit meiner Karte',
        'call_usual' => 'Zuerst rufe ich meinen Cousin unter seiner alten Nummer an',
        'transfer' => 'Ich überweise das Geld: Es ist dringend',
        'ask_amount' => 'Ich frage, wie viel er braucht, und schicke es',
    ],

    // die gefälschte E-Mail (mailbox_templates/fraud-*)
    'email_from' => 'Von',
    'email_dept' => 'Abteilung für Finanzbildung',
    'email_report' => 'Als Betrug melden',
    'emails' => [
        'verify' => ['subject' => 'DRINGEND: Ihr Konto wird gesperrt', 'hello' => 'Sehr geehrter Nutzer,',
            'text' => 'Wir haben ein Problem mit Ihrem Zedville-Konto festgestellt. Wenn Sie es nicht innerhalb von 24 Stunden bestätigen, wird es gesperrt und Sie erhalten Ihr Gehalt nicht.', 'button' => 'Mein Konto bestätigen'],
        'refund' => ['subject' => 'Sie haben eine Erstattung von :refund', 'hello' => 'Liebe Schülerin, lieber Schüler,',
            'text' => 'Auf Sie wartet eine Erstattung von :refund. Bestätigen Sie heute Ihr Konto, um sie zu erhalten: Danach verfällt sie.', 'button' => 'Erstattung erhalten'],
        'prize' => ['subject' => 'Herzlichen Glückwunsch! Sie haben ein Handy gewonnen', 'hello' => 'Lieber Gewinner,',
            'text' => 'Sie haben bei der Verlosung der Abteilung ein neues Handy gewonnen. Um es zu erhalten, zahlen Sie innerhalb von 48 Stunden nur :prize_fee Versand.', 'button' => 'Versand bezahlen'],
        'parcel' => ['subject' => 'Ihr Schulmaterial wird zurückgehalten', 'hello' => 'Liebe Schülerin, lieber Schüler,',
            'text' => 'Ihr Paket mit Schulmaterial wird zurückgehalten. Zahlen Sie heute :parcel_fee Gebühren, sonst wird es zurückgeschickt.', 'button' => 'Gebühren bezahlen'],
        'job' => ['subject' => 'Ein Job für Schüler: :pay pro Überweisung', 'hello' => 'Hallo!',
            'text' => 'Die Abteilung sucht Schüler, die Überweisungen auf ihr Konto erhalten und an andere Konten weiterleiten. Sie behalten :pay pro Überweisung. Keine Erfahrung nötig.', 'button' => 'Ich mache mit'],
    ],

    // die falsche Seite (fraud/fake)
    'fake_title' => 'Bestätigen Sie Ihr Konto',
    'fake_text' => 'Drücken Sie den Knopf, um fertig zu werden. Es dauert nur eine Sekunde.',

    // was passiert ist (das Ende des Anrufs, fraud/lesson)
    'safe_title' => 'Gut erkannt!',
    'safe_text' => 'Es war Betrug, und du hast richtig reagiert: Du bekommst einen Teilnahmepunkt.',
    'fell_title' => 'Es war Betrug',
    'fell_text' => 'Die Betrüger haben dir :amount abgenommen. Decke es innerhalb von 30 Tagen mit deinem Notgroschen oder einem Kredit, unter „:activity“.',
    'go_resolve' => 'Jetzt decken',
    'name' => 'So heißt es: :name.',
    'signs_title' => 'Die Warnzeichen',
    'good_title' => 'Was richtig gewesen wäre',
    'names' => [
        'bank_code' => 'Vishing, Betrug per Telefon',
        'safe_account' => 'Vishing, Betrug per Telefon',
        'tech_support' => 'der falsche Support-Anruf',
        'family' => 'der Messenger-Betrug („Hallo Mama“)',
        'verify' => 'Phishing, Betrug per E-Mail',
        'refund' => 'Phishing, Betrug per E-Mail',
        'prize' => 'Phishing, Betrug per E-Mail',
        'parcel' => 'Phishing, Betrug per E-Mail',
        'job' => 'Finanzagent werden',
    ],
    'signs' => [
        'bank_code' => 'Man fragt dich nach einem Code und setzt dich unter Druck. Die Nummer war die der Bank, aber eine Nummer lässt sich fälschen (Call-ID-Spoofing).',
        'safe_account' => 'Niemand von der Bank bittet dich, dein Geld zu verschieben. Die Nummer war die der Bank, aber eine Nummer lässt sich fälschen (Call-ID-Spoofing).',
        'tech_support' => 'Ein Anruf, um den du nicht gebeten hast, und du sollst etwas installieren und bezahlen.',
        'family' => 'Eine neue Nummer und Eile beim Geld.',
        'verify' => 'Die Adresse ist nicht die der Abteilung (eine 1 statt eines l), dein Name fehlt, und man setzt dich unter Druck.',
        'refund' => 'Geld, mit dem du nicht gerechnet hast, Eile und ein Link, um dein Konto zu „bestätigen“.',
        'prize' => 'Du sollst bezahlen, um einen Gewinn zu bekommen.',
        'parcel' => 'Eine kleine Zahlung über einen Link, in Eile.',
        'job' => 'Leichtes Geld dafür, fremdes Geld weiterzuleiten.',
    ],
    'good' => [
        'bank_code' => 'Auflegen und selbst die Bank anrufen, unter ihrer offiziellen Nummer (:phone).',
        'safe_account' => 'Auflegen. Deine Bank bittet dich nie, dein Geld zu verschieben.',
        'tech_support' => 'Auflegen.',
        'family' => 'Die alte Nummer anrufen oder etwas fragen, das nur diese Person weiß.',
        'verify' => 'Nicht auf den Link drücken: als Betrug melden. Die Abteilung bittet dich nie, dein Konto zu bestätigen.',
        'refund' => 'Löschen und melden.',
        'prize' => 'Ignorieren.',
        'parcel' => 'Nicht drücken: auf der offiziellen Website nachsehen.',
        'job' => 'Nein sagen: Das heißt, Finanzagent zu werden, und das ist eine Straftat.',
    ],
    'slogan' => 'Stoppen, Hinterfragen, Schützen.',
    'back' => 'Zurück zum Postfach',

    // die Hilfe der Bank (bank/bank-help)
    'bank_contact_title' => 'Die Telefonnummer der Universal Bank',
    'bank_contact_text' => 'Ruf uns an unter :phone. Wir fragen dich nie nach einem Code oder Passwort und bitten dich nie, dein Geld zu verschieben.',
];
