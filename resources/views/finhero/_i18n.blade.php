{{-- Language helpers for the FinHero tasks. The texts come from
     lang/<language>/finhero.php; numbers follow the page language. --}}
<script>
  const ZV_LANG = document.documentElement.lang || 'en';

  // Fills :placeholders - longest names first, so :amount is never cut by :a.
  function zvT(text, params) {
    Object.keys(params || {})
      .sort((a, b) => b.length - a.length)
      .forEach(name => { text = String(text).split(':' + name).join(params[name]); });
    return text;
  }

  // A number written the way the student's language writes it: 4,000 / 4.000 / 4 000.
  function zvNum(n, decimals) {
    const options = decimals === undefined ? {} : { minimumFractionDigits: decimals, maximumFractionDigits: decimals };
    return Number(n).toLocaleString(ZV_LANG, options);
  }

  // An amount of zeds: whole ones as they are (4,000), with cents always two digits (3,952.40).
  function zvMoney(n) {
    return zvNum(n, Number.isInteger(Number(n)) ? 0 : 2);
  }

  // Reads a typed number whatever separators the student used: "4,000",
  // "4.000", "4 000", "27,5", "27.5", "55%". A separator followed by exactly
  // three digits is a thousands separator; otherwise it's the decimal mark.
  function zvParseNumber(text) {
    let s = String(text).replace(/[^\d.,-]/g, '');
    const lastSep = Math.max(s.lastIndexOf(','), s.lastIndexOf('.'));
    if (lastSep !== -1 && s.length - lastSep - 1 !== 3) {
      s = s.slice(0, lastSep).replace(/[.,]/g, '') + '.' + s.slice(lastSep + 1);
    } else {
      s = s.replace(/[.,]/g, '');
    }
    return parseFloat(s);
  }

  // Hides the result of the last worked step ("1,100 + 500 = 1,600 zeds" -> "1,100 + 500 = ?").
  function zvRedactLastStep(stepsText) {
    const lines = stepsText.split("\n");
    lines[lines.length - 1] = lines[lines.length - 1].replace(/=[^=]*$/, "= ?");
    return lines.join("\n");
  }
</script>
