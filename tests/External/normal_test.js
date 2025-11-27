import http from "k6/http";
import { sleep } from "k6";

export const options = {
  stages: [
    { duration: "10s", target: 50 },    // Sube rápido a 50 usuarios
    { duration: "20s", target: 100 },   // Mantiene 100
    { duration: "15s", target: 300 },   // Pico fuerte
    { duration: "10s", target: 500 },   // Spike Attack
    { duration: "15s", target: 150 },   // Baja un poco
    { duration: "10s", target: 0 },     // Desacelera totalmente
  ],

  thresholds: {
    http_req_duration: ["p(95)<2000"],  
    http_req_failed: ["rate<0.01"],
  },
};

export default function () {
  const url = "http://127.0.0.1:8000/test-fast";

  const res = http.get(url);

  // Simula comportamiento humano ligero
  sleep(0.2);
}
