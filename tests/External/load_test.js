import http from "k6/http";
import { sleep } from "k6";

export const options = {
  stages: [
    { duration: "10s", target: 20 },
    { duration: "20s", target: 20 },
    { duration: "5s", target: 0 },
  ],
};

export default function () {
  const url = "http://127.0.0.1:8000/api/long-url";

  const payload = JSON.stringify({
    long_url: "https://www.google.com"
  });

  const params = {
    headers: {
      "Content-Type": "application/json",
    },
  };

  http.post(url, payload, params);
  sleep(1);
}
