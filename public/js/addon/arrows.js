(function (root) {
  var COLORS = ["#1f9d45", "#2f7fe0", "#e0a11a", "#d44848", "#8d5ad6"];

  function point(square, flipped) {
    var file = square.charCodeAt(0) - 97;
    var rank = Number(square[1]) - 1;
    var visualFile = flipped ? 7 - file : file;
    var rankFromTop = flipped ? rank : 7 - rank;
    return {
      x: (visualFile + 0.5) * 12.5,
      y: (rankFromTop + 0.5) * 12.5,
    };
  }

  function arrowsFor(lines, options) {
    var arrows = [];
    var count = Math.min(lines.length, options.multiPv || lines.length);
    for (var i = 0; i < count; i++) {
      var line = lines[i];
      var pv = line.pv || [];
      if (!pv[0]) continue;
      var color = COLORS[i % COLORS.length];
      arrows.push({
        from: pv[0].slice(0, 2),
        to: pv[0].slice(2, 4),
        color: color,
        kind: "candidate",
        rank: i,
        step: i === 0 ? 1 : 0,
        width: i === 0 ? 2.15 : 1.55,
        opacity: i === 0 ? 0.96 : 0.7,
      });
      var extra = i === 0 || options.continuationsOnAllLines ? options.continuationPlies : 0;
      for (var ply = 1; ply <= extra && ply < pv.length; ply++) {
        arrows.push({
          from: pv[ply].slice(0, 2),
          to: pv[ply].slice(2, 4),
          color: color,
          kind: "next",
          rank: i,
          step: ply + 1,
          yours: ply % 2 === 0,
          width: 1.45,
          opacity: 0.88,
        });
      }
    }
    return arrows;
  }

  function layout(arrows, flipped) {
    var groups = new Map();
    arrows.forEach(function (arrow, index) {
      var key = arrow.from + arrow.to;
      if (!groups.has(key)) groups.set(key, []);
      groups.get(key).push(index);
    });
    return arrows.map(function (arrow, index) {
      var from = point(arrow.from, flipped);
      var to = point(arrow.to, flipped);
      var dx = to.x - from.x;
      var dy = to.y - from.y;
      var len = Math.hypot(dx, dy) || 1;
      var ux = dx / len;
      var uy = dy / len;
      var px = -uy;
      var py = ux;
      var siblings = groups.get(arrow.from + arrow.to);
      var slot = siblings.indexOf(index);
      var offset = siblings.length > 1 ? (slot - (siblings.length - 1) / 2) * 1.15 : 0;
      from = { x: from.x + px * offset, y: from.y + py * offset };
      to = { x: to.x + px * offset, y: to.y + py * offset };
      var startPad = Math.min(3.1, len * 0.18);
      var headLen = Math.min(arrow.kind === "candidate" ? 3.15 : 2.35, len * 0.42);
      var headW = arrow.kind === "candidate" ? 1.55 : 1.12;
      var x1 = from.x + ux * startPad;
      var y1 = from.y + uy * startPad;
      var x2 = to.x - ux * (len > headLen + startPad ? 0.2 : 0);
      var y2 = to.y - uy * (len > headLen + startPad ? 0.2 : 0);
      var baseX = x2 - ux * headLen;
      var baseY = y2 - uy * headLen;
      var badgeAt = arrow.kind === "next" || arrow.step ? 0.62 : 0;
      return {
        color: arrow.color,
        width: arrow.width,
        opacity: arrow.opacity,
        kind: arrow.kind,
        step: arrow.step,
        yours: arrow.yours,
        x1: x1,
        y1: y1,
        x2: baseX,
        y2: baseY,
        head: [
          [x2, y2],
          [baseX + px * headW, baseY + py * headW],
          [baseX - px * headW, baseY - py * headW],
        ],
        badge: badgeAt
          ? {
              x: x1 + (x2 - x1) * badgeAt + px * 2.1,
              y: y1 + (y2 - y1) * badgeAt + py * 2.1,
            }
          : null,
      };
    });
  }

  function svgEl(name) {
    return document.createElementNS("http://www.w3.org/2000/svg", name);
  }

  function draw(svg, arrows, flipped, options) {
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    var ordered = arrows
      .map(function (arrow, index) { return { arrow: arrow, index: index }; })
      .sort(function (a, b) {
        if (a.arrow.kind === b.arrow.kind) return a.index - b.index;
        return a.arrow.kind === "next" ? -1 : 1;
      })
      .map(function (item) { return item.arrow; });
    var laid = layout(ordered, flipped);
    for (var i = 0; i < laid.length; i++) append(svg, laid[i], options || {});
  }

  function append(svg, arrow, options) {
    var shaft = svgEl("line");
    shaft.setAttribute("x1", arrow.x1.toFixed(2));
    shaft.setAttribute("y1", arrow.y1.toFixed(2));
    shaft.setAttribute("x2", arrow.x2.toFixed(2));
    shaft.setAttribute("y2", arrow.y2.toFixed(2));
    shaft.setAttribute("stroke", arrow.color);
    shaft.setAttribute("stroke-width", String(arrow.width));
    shaft.setAttribute("stroke-linecap", "round");
    shaft.setAttribute("opacity", String(arrow.opacity));
    svg.appendChild(shaft);

    var head = svgEl("polygon");
    head.setAttribute("points", arrow.head.map(function (p) { return p[0].toFixed(2) + "," + p[1].toFixed(2); }).join(" "));
    head.setAttribute("fill", arrow.color);
    head.setAttribute("opacity", String(arrow.opacity));
    svg.appendChild(head);

    if (!options.showNumbers || !arrow.step || !arrow.badge) return;
    var darkText = arrow.color === "#e0a11a";
    var circle = svgEl("circle");
    circle.setAttribute("cx", arrow.badge.x.toFixed(2));
    circle.setAttribute("cy", arrow.badge.y.toFixed(2));
    circle.setAttribute("r", "1.85");
    circle.setAttribute("fill", arrow.yours === false ? "#111" : arrow.color);
    circle.setAttribute("stroke", arrow.color);
    circle.setAttribute("stroke-width", "0.35");
    svg.appendChild(circle);

    var text = svgEl("text");
    text.setAttribute("x", arrow.badge.x.toFixed(2));
    text.setAttribute("y", arrow.badge.y.toFixed(2));
    text.setAttribute("text-anchor", "middle");
    text.setAttribute("dominant-baseline", "central");
    text.setAttribute("font-size", "2.15");
    text.setAttribute("font-family", "Segoe UI, sans-serif");
    text.setAttribute("font-weight", "700");
    text.setAttribute("fill", arrow.yours === false || darkText ? "#f4f1e6" : "#08140c");
    text.textContent = String(arrow.step);
    svg.appendChild(text);
  }

  root.BAArrows = {
    COLORS: COLORS,
    point: point,
    arrowsFor: arrowsFor,
    layout: layout,
    draw: draw,
  };
})(typeof self !== "undefined" ? self : globalThis);
