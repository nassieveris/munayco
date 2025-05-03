document.addEventListener('DOMContentLoaded', () => {
  [handleBurgerMenu, updateMenuClasses, populateSubjectField].forEach(fn => fn());
});

const handleBurgerMenu = () => {
  const burger = document.getElementById('burger');
  const burgerContainer = burger?.querySelector('.burger__container');
  const body = document.body;

  const toggleBurger = () => {
    body.classList.toggle('body_burger');
    burger?.classList.toggle('burger_active');
  };

  document.getElementById('headerBurger')?.addEventListener('click', toggleBurger);

  burger?.addEventListener('click', (e) => {
    if (!burgerContainer?.contains(e.target)) {
      body.classList.remove('body_burger');
      burger.classList.remove('burger_active');
    }
  });
};

const replaceClassPrefixes = (element) => {
  const classMappings = [
    { prefix: 'header__menu', replacement: 'burger__menu' },
    { prefix: 'header__buttons', replacement: 'burger__submenu' },
    { prefix: 'footer__menu', replacement: 'burger__submenu' },
    { prefix: 'menu__column', replacement: '', action: 'remove' }
  ];

  classMappings.forEach(({ prefix, replacement, action }) => {
    element.querySelectorAll(`[class*="${prefix}"]`).forEach(el => {
      [...el.classList]
        .filter(className => className.startsWith(prefix))
        .forEach(className => {
          if (action === 'remove') {
            el.classList.remove(className);
          } else {
            el.classList.replace(className, className.replace(prefix, replacement));
          }
        });
    });
  });
};

const updateMenuClasses = () => {
  const burger = document.getElementById('burger');
  if (!burger) return;

  // Reemplazo de prefijos de clases
  replaceClassPrefixes(burger);

  // Cambios específicos de clases
  burger.querySelector('.header__buttons')?.classList.replace('header__buttons', 'burger__menu');
  burger.querySelector('.footer__menu')?.classList.replace('footer__menu', 'burger__menu');
  burger.querySelector('.header__menu')?.classList.replace('header__menu', 'burger__menu_super');
};

const populateSubjectField = () => {
  const subjectField = document.querySelector('input[name="subject"]');
  subjectField?.value = document.title.split('|')[0]?.trim() ?? '';
  subjectField && (subjectField.readOnly = true);
};
