/**
 * Asset Constants — Amazonia Theme
 *
 * Patrón: Asset Constants (Centralized Asset Management)
 * Uso:    import { ASSETS } from './constants/assets.js'
 *         ASSETS.logos.principal
 *         ASSETS.images.hero.desktop
 *         ASSETS.images.carousel[0]
 *         ASSETS.videos.intro
 */

export const ASSETS = {
  logos: {
    principal: "",
    blanco: "",
    negro: "",
    icono: "",
  },

  images: {
    hero: {
      desktop: "",
      mobile: "",
      tablet: "",
    },

    carousel: [
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782520364/FUNAMU35_tmwaub.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782520379/FUNAMU19_cgmy52.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782520383/FUNAMU16_dj6zu2.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782520386/FUNAMU13_of54yq.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782520393/FUNAMU06_xnwxvb.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782522179/19_aezi4l.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782522176/21_ayfwxf.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782522172/23_ai7u9h.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782522166/27_e00eug.jpg",
      "https://res.cloudinary.com/dknuryv7i/image/upload/v1782522131/46_uu1j8o.jpg",
    ],

    comunidades: {
      portada:
        "https://res.cloudinary.com/dknuryv7i/image/upload/v1785098378/bannerComunidad_wiite4.webp",
      selva: "",
      artesanas:
        "https://res.cloudinary.com/dknuryv7i/image/upload/v1782525133/21_ayfwxf.webp",
      login: "",
    },

    secciones: {
      quienesSomos: "",
      mision: "",
      productos: "",
      contacto: "",
    },

    iconos: {
      crafts: "",
      rainforest: "",
    },
  },

  videos: {
    intro: "",
    tutorial: "",
  },
};

if (typeof window !== "undefined") {
  window.AMAZONIA_ASSETS = ASSETS;

  /**
   * Resolver de imágenes por atributo.
   * Cualquier <img data-asset="images.comunidades.portada"> toma su src del slot
   * correspondiente en ASSETS si tiene URL (CDN); si el slot está vacío, se
   * conserva el src local del HTML como fallback. Funciona en todas las páginas.
   */
  var resolveAssets = function () {
    document.querySelectorAll("img[data-asset]").forEach(function (img) {
      var url = img
        .getAttribute("data-asset")
        .split(".")
        .reduce(function (o, k) {
          return o && o[k] != null ? o[k] : null;
        }, ASSETS);
      if (url) img.src = url;
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", resolveAssets);
  } else {
    resolveAssets();
  }
}
