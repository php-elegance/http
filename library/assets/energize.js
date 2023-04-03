document.addEventListener("DOMContentLoaded", () => {
  window.onpopstate = () => location.reload();
  console.log("🦝");
  energize.core.run();
});

const energize = {
  element: {
    page: null,
    layout: null,
    content: null,
    update() {
      this.page = document.getElementById("energize_page");
      this.layout = document.getElementById("energize_layout");
      this.content = document.getElementById("energize_content");
    },
  },

  update: {
    content(el, content) {
      el.innerHTML = content;
      el.querySelectorAll("script").forEach((tag) => eval(tag.innerHTML));
    },
    location(url) {
      if (url != window.location)
        history.pushState({ urlPath: url }, null, url);
    },
    dinamcHead(head) {
      document.title = head.title;

      document.head
        .querySelector('meta[name="description"]')
        .setAttribute("content", head.description);

      document.head
        .querySelector('link[rel="icon"]')
        .setAttribute("href", head.favicon);
    },
  },

  core: {
    actions: {},
    register(query, action) {
      this.actions[query] = action;
    },
    run() {
      energize.element.update();
      Object.keys(this.actions).forEach((query) =>
        document.body.querySelectorAll(query).forEach((el) => {
          this.actions[query](el);
          el.removeAttribute("energize");
        })
      );
    },
  },

  action: {
    async redirect(href) {
      window.location.href = href;
    },
    async link(href, force = false) {
      if (!force && href == window.location) return;

      let infoLocation = new URL(window.location);
      let infoHref = new URL(href);

      if (infoLocation.hostname != infoHref.hostname) this.redirect(href);
      else {
        let headers = {
          "Energize-Request-Type": "link",
          "Energize-Base-Hash": energize.element.page.dataset.hash,
          "Energize-Layout-Hash": energize.element.layout.dataset.hash,
        };

        let resp = await this.api("get", href, null, headers);

        if (resp.status > 399) {
          let hrefRedirect = `${energizeRouteError}?status=${resp.status}`;
          if (href != hrefRedirect) this.link(hrefRedirect);
          return;
        }

        if (resp.data.render) {
          let elRender = document.getElementById(resp.data.render);
          elRender.dataset.hash = resp.data.hash;
          energize.update.content(elRender, resp.data.content);
          energize.update.location(href);
          energizeDinamicHead(resp.data.head);
          energize.core.run();
        }
      }
    },
    submitting: false,
    async submit(method, url, data) {
      if (!this.submitting) {
        this.submitting = true;
        let headers = { "Energize-Request-Type": "submit" };
        let resp = await this.api(method, url, data, headers);
        this.submitting = false;
        return resp;
      }
    },
    api(method, url, data = {}, headers = {}) {
      return new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);

        for (let key in headers) xhr.setRequestHeader(key, headers[key]);

        xhr.responseType = "json";

        xhr.onload = () => {
          let resp = xhr.response;

          if (xhr.getResponseHeader("New-Location")) {
            energize.action.link(xhr.getResponseHeader("New-Location"), true);
            resp = {
              elegance: true,
              staus: xhr.status,
              detail: {
                to: xhr.getResponseHeader("New-Location"),
              },
              data: {},
            };
          }

          if (!resp.elegance)
            resp = {
              elegance: false,
              staus: xhr.status,
              detail: {},
              data: resp,
            };

          resolve(resp);
        };

        xhr.send(data);
      });
    },
  },

  load: {
    script(src, callOnLoad = () => {}) {
      if (document.head.querySelectorAll(`script[src="${src}"]`).length > 0)
        return callOnLoad();
      let script = document.createElement("script");
      script.async = "true";
      script.src = src;
      script.onload = () => callOnLoad();
      document.head.appendChild(script);
    },
    vue(component, inId) {
      this.script("https://unpkg.com/vue@3.2.47/dist/vue.global.prod.js", () =>
        Vue.createApp(component).mount(`#${inId}`)
      );
    },
  },
};

energize.core.register("[href][energize]", (el) => {
  el.addEventListener("click", (ev) => {
    ev.preventDefault();
    energize.action.link(el.href);
  });
});

energize.core.register("form[energize]", (el) => {
  el.addEventListener("submit", async (ev) => {
    ev.preventDefault();

    let showmessage = el.querySelector(".energize-alert");

    if (showmessage) showmessage.innerHTML = "";

    let resp = await energize.action.submit(
      el.getAttribute("method") ?? "post",
      el.action,
      new FormData(el)
    );

    let action = el.getAttribute(resp.status > 399 ? "onerror" : "onsuccess");

    if (action) action = eval(action);

    if (action instanceof Function) return action(resp);

    if (showmessage) {
      let message = resp.detail.message ?? (resp.status > 399 ? "erro" : "ok");
      let description = resp.detail.description ?? "";
      showmessage.innerHTML =
        `<span class='sts_${resp.status}'>` +
        `<span>${message}</span>` +
        `<span>${description}</span>` +
        `</span>`;
    }
  });
});
