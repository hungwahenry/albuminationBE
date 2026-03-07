Release Group
Request: http://musicbrainz.org/ws/2/release-group/c9fdb94c-4975-4ed6-a96f-ef6d80bb7738?inc=artist-credits+releases

JSON Response
 {
     id: "c9fdb94c-4975-4ed6-a96f-ef6d80bb7738",
     title: "The Lost Tape",
     first-release-date: "2012-05-22",
     artist-credit: [
         "name": "50 Cent",
         "joinphrase": "",
         "artist": {
             "id": "8e68819d-71be-4e7d-b41d-f1df81b01d3f",
             "name": "50 Cent",
             "sort-name": "50 Cent",
             "disambiguation": ""
         }
     ],
     disambiguation: null,
     primary-type-id: "f529b476-6e62-324f-b0aa-1f3e33d313fc",
     primary-type: "Album",
     secondary-type-ids: ["15c1b1f5-d893-3375-a1db-e180c5ae15ed"]
     secondary-types: [ "Mixtape/Street" ],
     releases: [
         {
             "id": "2ec84eb6-ab92-4ac3-9720-32ad84c34f11",
             "title": "The Lost Tape",
             /* some properties omitted to keep this example shorter, see the release results for the full format */
         }
     ]
  }


Release
Request: http://musicbrainz.org/ws/2/release/59211ea4-ffd2-4ad9-9a4e-941d3148024a?inc=artist-credits+labels+discids+recordings

  {
    id: "59211ea4-ffd2-4ad9-9a4e-941d3148024a",
    title: "æ³o & h³æ",
    disambiguation: "",
    artist-credit: [
      {
        name: "Autechre",
        joinphrase: " & ",
        artist: {
          id: "410c9baf-5469-44f6-9852-826524b80c61",
          name: "Autechre",
          sort-name: "Autechre",
          disambiguation: "English electronic music duo Rob Brown & Sean Booth"
        }
      },
      {
        name: "The Hafler Trio",
        joinphrase: "",
        artist: {
          id: "146c01d0-d3a2-44c3-acb5-9208bce75e14",
          name: "The Hafler Trio",
          sort-name: "Hafler Trio, The",
          disambiguation: ""
        }
      }
    ],
    date: "2003-12-04",
    country: "GB",
    release-events: [
      {
        date: "2003-12-04",
        area: {
          id: "8a754a16-0027-3a29-b6d7-2b40ea0481ed",
          name: "United Kingdom",
          sort-name: "United Kingdom",
          iso-3166-1-codes: ["GB"],
          disambiguation: ""
        }
      }
    ],
    label-info: [
      {
        catalog-number: "pgram002",
        label: {
          id: "a0759efa-f583-49ea-9a8d-d5bbce55541c",
          name: "Phonometrography",
          disambiguation: "",
          label-code: null
        }
      }
    ],
    barcode: null,
    packaging-id: null,
    packaging: null,
    status-id: "4e304316-386d-3409-af2e-78857eec5cfe",
    status: "Official",
    quality: "normal",
    text-representation: {
      language: "eng",
      script: "Latn"
    },
    asin: null,
    media: [
      {
        discs: [
          {
            id: "nN2g3a0ZSjovyIgK3bJl6_.j8C4-",
            sectors: 73241,
            offsets: [150],
            offset-count: 1
          }
        ],
        position: 1,
        title: "æ³o",
        format-id: "9712d52a-4509-3d4b-a1a2-67c88c643e31",
        format: "CD",
        track-count: 1,
        track-offset: 0,
        tracks: [
          {
            id: "61af3e5a-14e0-350d-9826-a884c6e586b1",
            title: "æ³o",
            length: 974546,
            number: "1",
            position: 1,
            artist-credit: [
              {
                name: "Autechre",
                joinphrase: " & ",
                artist: {
                  id: "410c9baf-5469-44f6-9852-826524b80c61",
                  name: "Autechre",
                  sort-name: "Autechre",
                  disambiguation: "English electronic music duo Rob Brown & Sean Booth"
                }
              },
              {
                name: "The Hafler Trio",
                joinphrase: "",
                artist: {
                  id: "146c01d0-d3a2-44c3-acb5-9208bce75e14",
                  name: "The Hafler Trio",
                  sort-name: "Hafler Trio, The",
                  disambiguation: ""
                }
              }
            ],
            recording: {
              id: "af87f070-238b-46c1-aa3e-f831ab91fa20",
              title: "æ³o",
              disambiguation: "",
              length: 974546,
              video: false,
              artist-credit: [
                {
                  name: "Autechre",
                  joinphrase: " & ",
                  artist: {
                    id: "410c9baf-5469-44f6-9852-826524b80c61",
                    name: "Autechre",
                    sort-name: "Autechre",
                    disambiguation: "English electronic music duo Rob Brown & Sean Booth"
                  }
                },
                {
                  name: "The Hafler Trio",
                  joinphrase: "",
                  artist: {
                    id: "146c01d0-d3a2-44c3-acb5-9208bce75e14",
                    name: "The Hafler Trio",
                    sort-name: "Hafler Trio, The",
                    disambiguation: ""
                  }
                }
              ]
            }
          }
        ]
      },
      {
        position: 2,
        title: "h³æ",
        format-id: "9712d52a-4509-3d4b-a1a2-67c88c643e31",
        format: "CD",
        track-count: 1,
        track-offset: 0,
        discs: [
          {
            id: "aSHvkMnq2jZVFEK.DmSPbvN_f54-",
            sectors: 69341,
            offsets: [150],
            offset-count: 1
          }
        ],
        tracks: [
          {
            id: "5f2031a2-c67d-3bec-8ae5-8d22847ab0a5",
            title: "h³æ",
            length: 922546,
            number: "1",
            position: 1,
            artist-credit: [
              {
                name: "Autechre",
                joinphrase: " & ",
                artist: {
                  id: "410c9baf-5469-44f6-9852-826524b80c61",
                  name: "Autechre",
                  sort-name: "Autechre",
                  disambiguation: "English electronic music duo Rob Brown & Sean Booth"
                }
              },
              {
                name: "The Hafler Trio",
                joinphrase: "",
                artist: {
                  id: "146c01d0-d3a2-44c3-acb5-9208bce75e14",
                  name: "The Hafler Trio",
                  sort-name: "Hafler Trio, The",
                  disambiguation: ""
                }
              }
            ],
            recording: {
              id: "5aff6309-2e02-4a47-9233-32d7dcc9a960",
              title: "h³æ",
              disambiguation: "",
              length: 922546,
              video: false,
              artist-credit: [
                {
                  name: "Autechre",
                  joinphrase: " & ",
                  artist: {
                    id: "410c9baf-5469-44f6-9852-826524b80c61",
                    name: "Autechre",
                    sort-name: "Autechre",
                    disambiguation: "English electronic music duo Rob Brown & Sean Booth"
                  }
                },
                {
                  name: "The Hafler Trio",
                  joinphrase: "",
                  artist: {
                    id: "146c01d0-d3a2-44c3-acb5-9208bce75e14",
                    name: "The Hafler Trio",
                    sort-name: "Hafler Trio, The",
                    disambiguation: ""
                  }
                }
              ]
            }
          }
        ]
      }
    ],
    cover-art-archive: {
      count: 1,
      artwork: true,
      front: true,
      back: false,
      darkened: false
    }
  }
