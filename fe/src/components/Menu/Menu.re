open EloTypes;
open Svc;
open Js.Promise;
open BsReactstrap;

[%bs.raw {|require('./Menu.scss')|}];

type state = {menuOpened: bool};
type appSend = appContainerActions => unit;

type action =
  | ToggleMenu
  | CloseMenu
  | Logout(appSend);

let component = ReasonReact.reducerComponent("Menu");

let onLogoutSuccess = appSend => {
  appSend(SetIsLogged(false));
  ReasonReact.Router.push("/");
};

let onServiceSuccess = (appSend, send, json) =>
  json
  |> Json.Decode.field("logout", Json.Decode.bool)
  |> (
    isLogoutSuccessfully =>
      isLogoutSuccessfully ? onLogoutSuccess(appSend) : send(CloseMenu)
  );

let onError = err => {
  Js.Console.error(err);
};

let logoutSvc = (_state, appSend) => {
  ReasonReact.UpdateWithSideEffects(
    {menuOpened: false},
    ({send}) =>
      svcGet("auth/logout")
      |> then_(json => onServiceSuccess(appSend, send, json) |> resolve)
      |> catch(err => onError(err) |> resolve)
      |> ignore,
  );
};

let make = (~activePlace, ~appSend, _children) => {
  ...component,
  initialState: () => {menuOpened: false},
  reducer: (action, state) =>
    switch (action) {
    | ToggleMenu => ReasonReact.Update({menuOpened: !state.menuOpened})
    | CloseMenu => ReasonReact.Update({menuOpened: false})
    | Logout(appSend) => logoutSvc(state, appSend)
    },
  render: ({state, send}) =>
    <Navbar className="menu" color="light" light=true>
      <NavbarBrand href="/"> {"Eloapp" |> ReasonReact.string} </NavbarBrand>
      <Nav pills=true>
        <NavItem className="dropdown show">
          <NavLink
            className="nav-link dropdown-toggle"
            href="#"
            active={activePlace == STATISTICS}
            onClick={() => send(ToggleMenu)}>
            {"Menu" |> ReasonReact.string}
          </NavLink>
          <div
            className={"dropdown-menu" ++ (state.menuOpened ? " show" : "")}>
            <a
              className="dropdown-item"
              href="#rank_and_stats"
              onClick={_e => send(CloseMenu)}>
              {"Ranking" |> ReasonReact.string}
            </a>
            <a
              className="dropdown-item"
              href="#operations"
              onClick={_e => send(CloseMenu)}>
              {"Operations" |> ReasonReact.string}
            </a>
            <a
              className="dropdown-item"
              href="#statistics"
              onClick={_e => send(CloseMenu)}>
              {"Stats" |> ReasonReact.string}
            </a>
            <div className="dropdown-divider" />
            <a
              className="dropdown-item"
              href="#"
              onClick={_e => send(Logout(appSend))}>
              {"Logout" |> ReasonReact.string}
            </a>
          </div>
        </NavItem>
      </Nav>
    </Navbar>,
};