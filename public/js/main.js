/**
 * JavaScript principal del Sistema de Control de Asistencia
 * Autor: Sistema
 * Fecha: Noviembre 2024
 */

// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", function () {
  inicializarSistema();
});

/**
 * Inicializa todas las funcionalidades del sistema
 */
function inicializarSistema() {
  inicializarSidebar();
  inicializarTabs();
  inicializarModales();
  inicializarAlertas();
  inicializarFormularios();
  inicializarTablas();
}

/**
 * Gestión del Sidebar
 */
function inicializarSidebar() {
  const sidebarToggle = document.querySelector(".sidebar-toggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("collapsed");

      // En móviles, mostrar/ocultar el sidebar
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle("show");
      }
    });
  }

  // Cerrar sidebar en móviles al hacer clic fuera
  document.addEventListener("click", function (event) {
    if (window.innerWidth <= 768) {
      if (
        !sidebar.contains(event.target) &&
        !sidebarToggle.contains(event.target)
      ) {
        sidebar.classList.remove("show");
      }
    }
  });

  // Manejar resize de ventana
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      sidebar.classList.remove("show");
    }
  });
}

/**
 * Gestión de Tabs
 */
function inicializarTabs() {
  // Función para cambiar tabs
  window.cambiarTab = function (event, tabId) {
    event.preventDefault();

    // Ocultar todos los contenidos
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active");
    });

    // Remover clase active de todos los tabs
    document.querySelectorAll(".nav-tab").forEach((tab) => {
      tab.classList.remove("active");
    });

    // Mostrar el contenido seleccionado
    const targetContent = document.getElementById(tabId);
    if (targetContent) {
      targetContent.classList.add("active");
    }

    // Activar el tab seleccionado
    event.target.classList.add("active");
  };
}

/**
 * Gestión de Modales
 */
function inicializarModales() {
  // Función para mostrar modal
  window.mostrarModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "block";
    }
  };

  // Función para cerrar modal
  window.cerrarModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "none";
    }
  };

  // Cerrar modal al hacer clic fuera
  window.addEventListener("click", function (event) {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = "none";
    }
  });

  // Cerrar modal con botón X
  document.querySelectorAll(".close").forEach((closeBtn) => {
    closeBtn.addEventListener("click", function () {
      const modal = this.closest(".modal");
      if (modal) {
        modal.style.display = "none";
      }
    });
  });

  // Cerrar modal con tecla Escape
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      document.querySelectorAll(".modal").forEach((modal) => {
        if (modal.style.display === "block") {
          modal.style.display = "none";
        }
      });
    }
  });
}

/**
 * Gestión de Alertas
 */
function inicializarAlertas() {
  // Auto-cerrar alertas después de 5 segundos
  setTimeout(function () {
    const alertas = document.querySelectorAll(".alert");
    alertas.forEach(function (alerta) {
      alerta.style.transition = "opacity 0.5s";
      alerta.style.opacity = "0";
      setTimeout(() => {
        if (alerta.parentNode) {
          alerta.parentNode.removeChild(alerta);
        }
      }, 500);
    });
  }, 5000);

  // Función para mostrar alertas dinámicamente
  window.mostrarAlerta = function (mensaje, tipo = "info") {
    const alertaDiv = document.createElement("div");
    alertaDiv.className = `alert alert-${tipo}`;
    alertaDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;

    const container = document.querySelector(".container");
    if (container) {
      container.insertBefore(alertaDiv, container.firstChild);
    }

    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
      if (alertaDiv.parentNode) {
        alertaDiv.style.opacity = "0";
        setTimeout(() => alertaDiv.remove(), 500);
      }
    }, 5000);
  };
}

/**
 * Gestión de Formularios
 */
function inicializarFormularios() {
  // Validación básica de formularios
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", function (event) {
      if (!validarFormulario(this)) {
        event.preventDefault();
      }
    });
  });

  // Confirmar eliminaciones
  window.confirmarEliminacion = function (mensaje) {
    return confirm(
      mensaje || "¿Estás seguro de que deseas eliminar este elemento?"
    );
  };

  // Función para validar formularios
  function validarFormulario(form) {
    let valido = true;
    const campos = form.querySelectorAll(
      "input[required], select[required], textarea[required]"
    );

    campos.forEach((campo) => {
      if (!campo.value.trim()) {
        campo.style.borderColor = "#e74c3c";
        valido = false;
      } else {
        campo.style.borderColor = "#ddd";
      }
    });

    return valido;
  }
}

/**
 * Gestión de Tablas
 */
function inicializarTablas() {
  // Hacer tablas responsivas
  document.querySelectorAll(".table").forEach((table) => {
    if (!table.closest(".table-responsive")) {
      const wrapper = document.createElement("div");
      wrapper.className = "table-responsive";
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
    }
  });
}

/**
 * Utilidades AJAX
 */
const Ajax = {
  /**
   * Realizar petición GET
   */
  get: function (url, callback) {
    fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => callback(null, data))
      .catch((error) => callback(error, null));
  },

  /**
   * Realizar petición POST
   */
  post: function (url, data, callback) {
    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => callback(null, data))
      .catch((error) => callback(error, null));
  },

  /**
   * Realizar petición PUT
   */
  put: function (url, data, callback) {
    fetch(url, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => callback(null, data))
      .catch((error) => callback(error, null));
  },

  /**
   * Realizar petición DELETE
   */
  delete: function (url, callback) {
    fetch(url, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => callback(null, data))
      .catch((error) => callback(error, null));
  },
};

/**
 * Utilidades del Sistema
 */
const Sistema = {
  /**
   * Formatear fecha
   */
  formatearFecha: function (fecha, formato = "dd/mm/yyyy") {
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, "0");
    const mes = String(date.getMonth() + 1).padStart(2, "0");
    const año = date.getFullYear();

    switch (formato) {
      case "dd/mm/yyyy":
        return `${dia}/${mes}/${año}`;
      case "yyyy-mm-dd":
        return `${año}-${mes}-${dia}`;
      default:
        return `${dia}/${mes}/${año}`;
    }
  },

  /**
   * Formatear hora
   */
  formatearHora: function (hora) {
    const date = new Date(hora);
    return date.toLocaleTimeString("es-ES", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });
  },

  /**
   * Mostrar loading
   */
  mostrarLoading: function (elemento) {
    const loading = document.createElement("div");
    loading.className = "loading";
    loading.id = "sistema-loading";

    if (typeof elemento === "string") {
      elemento = document.querySelector(elemento);
    }

    if (elemento) {
      elemento.appendChild(loading);
    }
  },

  /**
   * Ocultar loading
   */
  ocultarLoading: function () {
    const loading = document.getElementById("sistema-loading");
    if (loading) {
      loading.remove();
    }
  },

  /**
   * Refrescar página después de un tiempo
   */
  autoRefresh: function (tiempo = 30000) {
    setTimeout(function () {
      location.reload();
    }, tiempo);
  },

  /**
   * Validar email
   */
  validarEmail: function (email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  },

  /**
   * Validar teléfono
   */
  validarTelefono: function (telefono) {
    const regex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
    return regex.test(telefono);
  },

  /**
   * Copiar al portapapeles
   */
  copiarAlPortapapeles: function (texto) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(texto).then(() => {
        mostrarAlerta("Texto copiado al portapapeles", "success");
      });
    } else {
      // Fallback para navegadores antiguos
      const textArea = document.createElement("textarea");
      textArea.value = texto;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand("copy");
      document.body.removeChild(textArea);
      mostrarAlerta("Texto copiado al portapapeles", "success");
    }
  },
};

/**
 * Gestión de Estado Local
 */
const EstadoLocal = {
  /**
   * Guardar en localStorage
   */
  guardar: function (clave, valor) {
    try {
      localStorage.setItem(clave, JSON.stringify(valor));
      return true;
    } catch (error) {
      console.error("Error al guardar en localStorage:", error);
      return false;
    }
  },

  /**
   * Obtener de localStorage
   */
  obtener: function (clave) {
    try {
      const valor = localStorage.getItem(clave);
      return valor ? JSON.parse(valor) : null;
    } catch (error) {
      console.error("Error al obtener de localStorage:", error);
      return null;
    }
  },

  /**
   * Eliminar de localStorage
   */
  eliminar: function (clave) {
    try {
      localStorage.removeItem(clave);
      return true;
    } catch (error) {
      console.error("Error al eliminar de localStorage:", error);
      return false;
    }
  },

  /**
   * Limpiar localStorage
   */
  limpiar: function () {
    try {
      localStorage.clear();
      return true;
    } catch (error) {
      console.error("Error al limpiar localStorage:", error);
      return false;
    }
  },
};

/**
 * Eventos específicos para dashboards
 */
document.addEventListener("DOMContentLoaded", function () {
  // Auto-refresh para dashboards (cada 30 segundos)
  if (document.querySelector(".stats-grid")) {
    Sistema.autoRefresh(30000);
  }

  // Inicializar DataTables si está disponible
  if (typeof $ !== "undefined" && $.fn.DataTable) {
    $(".table").each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({
          pageLength: 10,
          lengthChange: true,
          searching: true,
          ordering: true,
          info: true,
          autoWidth: false,
          responsive: true,
          language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
          },
        });
      }
    });
  }
});

// Exportar para uso global
window.Sistema = Sistema;
window.Ajax = Ajax;
window.EstadoLocal = EstadoLocal;
