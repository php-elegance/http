document.addEventListener("DOMContentLoaded", () => {
  window.onpopstate = () => location.reload();
  console.log("🦝");
  front.core.run();
});

const front = {
  element: {
    base: null,
    layout: null,
    content: null,
    update() {
      this.base = document.getElementById("__base");
      this.layout = document.getElementById("__layout");
      this.content = document.getElementById("__content");
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
    async link(href) {
      if (href == window.location) return;

      let headers = {
        "Front-Request": "link",
        "Front-Base-Hash": front.element.base.dataset.hash,
        "Front-Layout-Hash": front.element.layout.dataset.hash,
      };

      let resp = await this.api("get", href, null, headers);

      if (!resp.elegance) return this.redirect(href);

      if (resp.error) return this.redirect(`${fError}?status=${resp.status}`);

      resp = resp.data;

      front.update.location(href);

      let elRender = document.getElementById(resp.render);

      elRender.dataset.hash = resp.hash;

      front.update.content(elRender, resp.content);
      front.update.dinamcHead(resp.head);

      front.core.run();
    },
    submitting: false,
    async submit(method, url, data) {
      if (!this.submitting) {
        this.submitting = true;
        let headers = { "Front-Request": "submit" };
        let resp = await this.api(method, url, data, headers);
        this.submitting = false;
        return resp;
      } else {
        console.log("trabalhando...");
      }
    },
    async api(method, url, data = {}, headers = {}) {
      let options = {};
      options["headers"] = headers;
      if (data instanceof FormData) data = Object.fromEntries(data);
      switch (method) {
        case "get":
          break;
        default:
          options["method"] = method;
          options["body"] = JSON.stringify(data);
          break;
      }

      return await fetch(url, options)
        .then(async (recived) => {
          let resp = await recived.json();
          return resp.elegance
            ? resp
            : {
                elegance: false,
                staus: recived.status,
                data: {
                  resp,
                },
              };
        })
        .catch(() => {
          return {
            elegance: false,
            staus: 500,
            error: true,
            data: [],
          };
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

    let showmessage = el.querySelector("span.front-alert");

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
      let message = resp.data.message ?? (resp.error ? "erro" : "ok");
      let description = resp.data.description ?? "";
      showmessage.innerHTML =
        `<span class='status_${resp.status} type_${resp.origin}'>` +
        `<span>${message}</span>` +
        `<span>${description}</span>` +
        `</span>`;
    }

    console.log(resp);
  });
});
