(function (root) {
  function parseInfo(line) {
    if (typeof line !== "string") return null;
    if (line.indexOf("info ") !== 0) return null;
    if (line.indexOf(" pv ") === -1) return null;
    if (line.indexOf(" currmove ") !== -1) return null;

    var depthMatch = /(?:^|\s)depth (\d+)/.exec(line);
    var pvMatch = /(?:^|\s)multipv (\d+)/.exec(line);
    var mateMatch = /(?:^|\s)score mate (-?\d+)/.exec(line);
    var cpMatch = /(?:^|\s)score cp (-?\d+)/.exec(line);
    var movesMatch = /(?:^|\s)pv (.+)$/.exec(line);
    if (!depthMatch || !movesMatch) return null;

    var pv = movesMatch[1].trim().split(/\s+/).filter(function (token) {
      return /^[a-h][1-8][a-h][1-8][qrbn]?$/.test(token);
    });
    if (!pv.length) return null;

    return {
      depth: Number(depthMatch[1]),
      multipv: pvMatch ? Number(pvMatch[1]) : 1,
      mate: mateMatch ? Number(mateMatch[1]) : null,
      cp: cpMatch ? Number(cpMatch[1]) : null,
      pv: pv,
    };
  }

  function toWhite(info, turn) {
    if (info.mate != null) {
      var mate = turn === "b" ? -info.mate : info.mate;
      return {
        mate: mate,
        cp: mate > 0 ? 100000 - mate * 10 : -100000 - mate * 10,
      };
    }
    var cp = info.cp || 0;
    return { mate: null, cp: turn === "b" ? -cp : cp };
  }

  function formatEval(line) {
    if (!line) return "…";
    if (line.mate != null && line.mate !== 0) {
      return (line.mate > 0 ? "+M" : "-M") + Math.abs(line.mate);
    }
    var pawns = (line.cp || 0) / 100;
    if (Math.abs(pawns) < 0.005) return "0.00";
    return (pawns > 0 ? "+" : "") + pawns.toFixed(2);
  }

  function whiteShare(line) {
    if (!line) return 50;
    if (line.mate != null && line.mate !== 0) return line.mate > 0 ? 100 : 0;
    var cp = Math.max(-1000, Math.min(1000, line.cp || 0));
    var win = 50 + 50 * (2 / (1 + Math.exp(-0.00368208 * cp)) - 1);
    return Math.max(0, Math.min(100, win));
  }

  root.BAUci = {
    parseInfo: parseInfo,
    toWhite: toWhite,
    formatEval: formatEval,
    whiteShare: whiteShare,
  };
})(typeof self !== "undefined" ? self : globalThis);
