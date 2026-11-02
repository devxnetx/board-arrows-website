(function (root) {
  function lineToSan(fen, ucis) {
    if (typeof Chess !== "function") return [];
    var chess;
    try {
      chess = new Chess(fen);
    } catch (err) {
      return [];
    }
    var sans = [];
    for (var i = 0; i < ucis.length; i++) {
      var uci = ucis[i];
      if (!/^[a-h][1-8][a-h][1-8][qrbn]?$/.test(uci)) break;
      var move = chess.move({
        from: uci.slice(0, 2),
        to: uci.slice(2, 4),
        promotion: uci[4] || undefined,
      });
      if (!move) break;
      sans.push(move.san);
    }
    return sans;
  }

  function formatLine(sans, turn) {
    if (!sans.length) return "";
    var parts = [];
    var index = 0;
    var number = 1;
    if (turn === "b") {
      parts.push("1…" + sans[0]);
      index = 1;
      number = 2;
    }
    for (; index < sans.length; index += 2) {
      var text = number + ". " + sans[index];
      if (sans[index + 1]) text += " " + sans[index + 1];
      parts.push(text);
      number += 1;
    }
    return parts.join(" ");
  }

  root.BASan = { lineToSan: lineToSan, formatLine: formatLine };
})(typeof self !== "undefined" ? self : globalThis);
