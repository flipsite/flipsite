ready(() => {
  const currentURL = window.location.href;
  document.querySelectorAll('[data-did-you-mean]').forEach((el) => {
    const root = el.getAttribute('data-root');
    const currentPage = window.location.href.replace(root+'/','').split('#')[0];
    if ('404' === currentPage) {
      el.innerHTML = el.getAttribute('data-did-you-mean');
      return;
    }
    let min = 99999;
    let page = ''
    for (let i = 0; i < sitemap.length; i++) {
      const dist = levenshtein(sitemap[i],currentPage);
      if (dist < min) {
        min = dist;
        page = sitemap[i];
      }
    }
    if (min < 10) {
      const link = el.querySelector('a');
      link.setAttribute('href',root+'/'+page);
      link.innerHTML = page;
      el.querySelector('strong').innerHTML = currentPage;
    } else {
      el.innerHTML = el.getAttribute('data-did-you-mean');
    }
  });
});
function levenshtein(a, b) {
  const matrix = [];
  // Increment along the first column of each row
  for (let i = 0; i <= b.length; i++) {
    matrix[i] = [i];
  }
  // Increment each column in the first row
  for (let j = 0; j <= a.length; j++) {
    matrix[0][j] = j;
  }
  // Fill the rest of the matrix
  for (let i = 1; i <= b.length; i++) {
    for (let j = 1; j <= a.length; j++) {
      if (b.charAt(i - 1) === a.charAt(j - 1)) {
        matrix[i][j] = matrix[i - 1][j - 1];
      } else {
        matrix[i][j] = Math.min(matrix[i - 1][j - 1] + 1, // substitution
                                matrix[i][j - 1] + 1,     // insertion
                                matrix[i - 1][j] + 1);    // deletion
      }
    }
  }
  return matrix[b.length][a.length];
}