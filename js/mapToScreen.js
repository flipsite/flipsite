ready(() => {
  const mapToScreen = () => {
      document.querySelectorAll('[data-screen-map]').forEach((el) => {
        const screenshot = el;
        const screenMap = el.getAttribute('data-screen-map').split(',').map(Number);
        const device = el.parentNode;

        
        const computeMatrix3dWithDeltas = function (src, deltas) {
          // build destination points by adding deltas
          const dst = src.map((p, i) => ({
            x: p.x + (deltas[i]?.x || 0),
            y: p.y + (deltas[i]?.y || 0)
          }));

          // build linear system
          const A = [], B = [];
          for (let i = 0; i < 4; i++) {
            const {x: x0, y: y0} = src[i];
            const {x: x1, y: y1} = dst[i];
            A.push([x0, y0, 1, 0, 0, 0, -x1*x0, -x1*y0]);
            A.push([0, 0, 0, x0, y0, 1, -y1*x0, -y1*y0]);
            B.push(x1, y1);
          }

          // Gaussian elimination solver
          function solve(A, b) {
            const m = A.length, n = A[0].length;
            const M = A.map((row, i) => [...row, b[i]]);
            for (let i = 0; i < n; i++) {
              let maxRow = i;
              for (let k = i+1; k < m; k++) if (Math.abs(M[k][i]) > Math.abs(M[maxRow][i])) maxRow = k;
              [M[i], M[maxRow]] = [M[maxRow], M[i]];
              for (let k = i+1; k < m; k++) {
                const c = M[k][i] / M[i][i];
                for (let j = i; j <= n; j++) M[k][j] -= c * M[i][j];
              }
            }
            const x = Array(n).fill(0);
            for (let i = n-1; i >= 0; i--) {
              x[i] = M[i][n] / M[i][i];
              for (let k = i-1; k >= 0; k--) M[k][n] -= M[k][i] * x[i];
            }
            return x;
          }

          const h = solve(A, B);
          h.push(1);
          const [a,b,c,d,e,f,g,h_] = h;
          return [
            a, d, 0, g,
            b, e, 0, h_,
            0, 0, 1, 0,
            c, f, 0, 1
          ];
        }
        const src = [
          {x:0,   y:0},
          {x:screenshot.offsetWidth, y:0},
          {x:screenshot.offsetWidth, y:screenshot.offsetHeight},
          {x:0,   y:screenshot.offsetHeight}
        ];
        screenshot.style.transform = `matrix3d(${computeMatrix3dWithDeltas(src, [
            {x: screenMap[0] * device.offsetWidth,                          y: screenMap[1] * device.offsetHeight},
            {x: device.offsetWidth * screenMap[2] - screenshot.offsetWidth, y: screenMap[3] * device.offsetHeight},
            {x: device.offsetWidth * screenMap[4] - screenshot.offsetWidth, y: screenMap[5] * device.offsetHeight - screenshot.offsetHeight},
            {x: screenMap[6] * device.offsetWidth,                          y: screenMap[7] * device.offsetHeight - screenshot.offsetHeight},  
          ])})`;
      });
  };
  mapToScreen();
  window.addEventListener('resize', mapToScreen);
});
