open Js.Promise;

let svcUrl = "api/";

exception InvalidArgumentException;

let svcGeneric = (method, resource, payloadOption) => {
  let body =
    switch (method, payloadOption) {
    | (Fetch.Get, None) => None
    | (_, Some(payload)) =>
      Some(Fetch.BodyInit.make(Js.Json.stringify(payload)))
    | _ => raise(InvalidArgumentException)
    };

  Fetch.fetchWithInit(
    svcUrl ++ resource,
    Fetch.RequestInit.make(
      ~method_=method,
      ~credentials=SameOrigin,
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      ~body?,
      (),
    ),
  )
  |> then_(Fetch.Response.json);
};

let svcGet = resource => svcGeneric(Fetch.Get, resource, None);

let svcPost = (resource, payload) =>
  svcGeneric(Fetch.Post, resource, Some(payload));
let svcPut = (resource, payload) =>
  svcGeneric(Fetch.Put, resource, Some(payload));
let svcDelete = (resource, payload) =>
  svcGeneric(Fetch.Delete, resource, Some(payload));