[@bs.deriving abstract]
type randomColorOptions = {
  [@bs.optional]
  seed: string,
  [@bs.optional]
  hue: string,
  [@bs.optional]
  format: string, // rgb, rgba, rgbArray, hsl, hsla, hslArray and hex (default).
  [@bs.optional]
  luminosity: string, // bright, light or dark
  [@bs.optional]
  count: int,
  [@bs.optional]
  alpha: float // between 0 and 1
  //  [@bs.optional]
  //  hue:
  //    | "red"
  //    | "orange"
  //    | "yellow"
  //    | "green"
  //    | "blue"
  //    | "purple"
  //    | "pink"
  //    | "monochrome",
};

[@bs.module]
external randomColor: randomColorOptions => string = "randomcolor";
