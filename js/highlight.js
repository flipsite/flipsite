ready(() => {
  const script = document.createElement('script');
  script.src = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.0/highlight.min.js';
  script.async = true;
  let theme = ''
  script.onload = () => {
    document.querySelectorAll('pre.code').forEach((block) => {
      if (block.hasAttribute('data-hljs-theme')) {
        theme = block.getAttribute('data-hljs-theme');
      }
      hljs.highlightElement(block);
    });
    if (!theme) theme = 'default'
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.0/styles/'+theme+'.min.css';
    document.head.appendChild(link);
  };
  document.head.appendChild(script);
});