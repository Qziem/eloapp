let defaultPlace = "#rank_and_stats";

let setIfUrlEmpty = () => {
  let currentUrl = ReasonReact.Router.dangerouslyGetInitialUrl();
  currentUrl.hash === "" ? ReasonReact.Router.push(defaultPlace) : ();
};