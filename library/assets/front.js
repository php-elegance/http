document.addEventListener("DOMContentLoaded", () => {
    window.onpopstate = () => location.reload();
    console.log("⚡");
    front.core.run();
});

const front = {
    core: {
        URL_VUE_JS: "https://unpkg.com/vue@3.2.47/dist/vue.global.prod.js",
        BASE_HOST: (new URL(window.location)).hostname,
        WORKING: false,
        REGISTRED: {},
        run() {
            Object.keys(front.core.REGISTRED).forEach((querySelector) =>
                document.body.querySelectorAll(querySelector).forEach((el) => {
                    front.core.REGISTRED[querySelector](el);
                    el.removeAttribute("front");
                })
            );
        },
        register(querySelector, action) {
            front.core.REGISTRED[querySelector] = action
        },
        solve: (action) => new Promise(async (resolve, reject) => {
            if (!front.core.WORKING) {
                front.core.WORKING = true;
                document.body.classList.add('front-working');
                let resp = await action();
                front.core.WORKING = false
                document.body.classList.remove('front-working');
                return resolve(resp)
            }
            return reject('awaiting');
        }),
        update: {
            content(content) {
                let el = document.getElementById('front_content')
                el.innerHTML = content;
                el.querySelectorAll("script").forEach((tag) => eval(tag.innerHTML));
                front.core.run();
            },
            layout(content, hash) {
                let el = document.getElementById('front_layout')
                el.innerHTML = content;
                el.dataset.hash = hash;
                el.querySelectorAll("script").forEach((tag) => eval(tag.innerHTML));
                front.core.run();
            },
            location(url) {
                if (url != window.location)
                    history.pushState({ urlPath: url }, null, url);
            },
            head(head) {
                document.title = head.title;
                document.head.querySelector('meta[name="description"]').setAttribute("content", head.description);
                document.head.querySelector('link[rel="icon"]').setAttribute("href", head.favicon);
            }
        },
        load: {
            script(src, callOnLoad = () => { }) {
                if (document.head.querySelectorAll(`script[src="${src}"]`).length > 0) return callOnLoad();
                let script = document.createElement("script");
                script.async = "true";
                script.src = src;
                script.onload = () => callOnLoad();
                document.head.appendChild(script);
            },
            vue(component, inId) {
                front.core.load.script(front.core.URL_VUE_JS, () => Vue.createApp(component).mount(inId));
            },
        },
    },
    go: (url) => new Promise(async (resolve, reject) => {
        if (url == window.location) return;

        if ((new URL(url)).hostname != front.core.BASE_HOST)
            return await front.redirect(url);

        let hash = document.getElementById('front_layout').dataset.hash;

        let resp = await front.request('get', url, {}, { 'Front-Hash': hash });

        if (!resp.elegance)
            return await front.redirect(url);

        if (resp.error)
            return;

        front.core.update.head(resp.data.head);

        front.core.update.location(url);

        if (resp.data.hash == hash) {
            front.core.update.content(resp.data.content)
        } else {
            front.core.update.layout(resp.data.content, resp.data.hash)
        }

        return;
    }),
    submit: (method, url = null, data = {}) => new Promise(async (resolve, reject) => {
        return resolve(await front.request(method, url, data))
    }),
    request: (method, url = null, data = {}, header = {}) => front.core.solve(() =>
        new Promise((resolve, reject) => {
            var xhr = new XMLHttpRequest();

            url = url ?? window.location.href

            xhr.open(method, url, true);

            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.setRequestHeader("Front-Request", method);

            for (let key in header) xhr.setRequestHeader(key, header[key]);

            if (data instanceof FormData) data = Object.fromEntries(data);
            data = JSON.stringify({ ...data });

            xhr.responseType = "json";

            xhr.onload = async () => {
                let resp = xhr.response;

                if (xhr.getResponseHeader("Front-Location")) {
                    front.core.WORKING = false;
                    return resolve(await front.go(xhr.getResponseHeader("Front-Location"), true));
                }

                if (!resp.elegance) resp = {
                    elegance: false,
                    error: xhr.status > 399,
                    staus: xhr.status,
                    detail: {},
                    data: resp,
                };

                return resolve(resp);
            };

            xhr.send(data);
        })
    ),
    redirect: (url) => new Promise((resolve, reject) => {
        window.location.href = url;
        return resolve('ok');
    }),
};

front.core.register("[href][front]", (el) => {
    el.addEventListener("click", (ev) => {
        ev.preventDefault();
        front.go(el.href);
    });
});

front.core.register("form[front]", (el) => {
    el.addEventListener("submit", async (ev) => {
        ev.preventDefault();

        let showmessage = el.querySelector(".front-alert");

        if (showmessage) showmessage.innerHTML = "";

        let resp = await front.submit(
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

front.core.register('[href]:not([href=""])', (el) => {
    if (el.href == window.location.href)
        el.classList.add('front-active-link');
    else
        el.classList.remove('front-active-link')
})