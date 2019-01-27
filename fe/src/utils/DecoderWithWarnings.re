open EloTypes;
open Json.Decode;

exception IllegalCombinationDecodingResult;

module type ContentDecoder = {
  type content;
  let contentKey: string;
  let decode: Js.Json.t => content;
};

module MakeDecoderWithWarningsOrContent = (ContentDecoder: ContentDecoder) => {
  let decode =
      (json: Js.Json.t): resultWarningOrContent(ContentDecoder.content) => {
    let status = json |> field("status", string);
    let content =
      json
      |> optional(field(ContentDecoder.contentKey, ContentDecoder.decode));
    let warningMsg = json |> optional(field("warningMsg", string));

    switch (status, content, warningMsg) {
    | ("success", Some(c), None) => SUCCESS(c)
    | ("warning", None, Some(msg)) => WARNING(msg)
    | _ => raise(IllegalCombinationDecodingResult)
    };
  };
};

module DecoderWithWarningsOrEmpty = {
  let decode = json: resultWarningOrEmpty => {
    let status = json |> field("status", string);
    let warningMsg = json |> optional(field("warningMsg", string));

    switch (status, warningMsg) {
    | ("success", None) => SUCCESS
    | ("warning", Some(msg)) => WARNING(msg)
    | _ => raise(IllegalCombinationDecodingResult)
    };
  };
};