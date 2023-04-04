document.addEventListener("DOMContentLoaded", () => {
  window.onpopstate = () => location.reload();
  console.log("🦝");
  front.core.run();
});

const front = {
  element: {
    page: null,
    layout: null,
    content: null,
    update() {
      this.page = document.getElementById("front_page");
      this.layout = document.getElementById("front_layout");
      this.content = document.getElementById("front_content");
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
      front.element.update();
      Object.keys(this.actions).forEach((query) =>
        document.body.querySelectorAll(query).forEach((el) => {
          this.actions[query](el);
          el.removeAttribute("front");
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
          "Front-Request-Type": "link",
          "Front-Base-Hash": front.element.page.dataset.hash,
          "Front-Layout-Hash": front.element.layout.dataset.hash,
        };

        let resp = await this.api("get", href, null, headers);

        if (resp.error) {
          let hrefRedirect = `${frontRouteError}?status=${resp.status}`;
          if (href != hrefRedirect) this.link(hrefRedirect);
          return;
        }

        if (resp.data.render) {
          let elRender = document.getElementById(resp.data.render);
          elRender.dataset.hash = resp.data.hash;
          front.update.content(elRender, resp.data.content);
          front.update.location(href);
          frontDinamicHead(resp.data.head);
          front.core.run();
        }
      }
    },
    submitting: false,
    async submit(method, url, data) {
      if (!this.submitting) {
        this.submitting = true;
        let headers = { "Front-Request-Type": "submit" };
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
            front.action.link(xhr.getResponseHeader("New-Location"), true);
            resp = {
              elegance: true,
              error: xhr.status > 399,
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
              error: xhr.status > 399,
              staus: xhr.status,
              detail: {},
              data: resp,
            };

          resolve(resp);
        };

        if (!(data instanceof FormData)) {
          var newData = new FormData();
          for (let key in data) newData.append(key, data[key]);
          data = newData;
        }

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

front.core.register("[href][front]", (el) => {
  el.addEventListener("click", (ev) => {
    ev.preventDefault();
    front.action.link(el.href);
  });
});

front.core.register("form[front]", (el) => {
  el.addEventListener("submit", async (ev) => {
    ev.preventDefault();

    let showmessage = el.querySelector(".front-alert");

    if (showmessage) showmessage.innerHTML = "";

    let resp = await front.action.submit(
      el.getAttribute("method") ?? "post",
      el.action,
      new FormData(el)
    );

    let action = el.getAttribute(resp.error ? "onerror" : "onsuccess");

    if (action) action = eval(action);

    if (action instanceof Function) return action(resp);

    if (showmessage) {
      let spanClass = `sts_` + (resp.error ? "erro" : "success");
      let message = resp.detail.message ?? (resp.error ? "erro" : "ok");
      let description = resp.detail.description ?? "";
      showmessage.innerHTML =
        `<span class='sts_${resp.status} ${spanClass}'>` +
        `<span>${message}</span>` +
        `<span>${description}</span>` +
        `</span>`;
    }
  });
});
