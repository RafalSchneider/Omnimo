import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { postsService } from "../services/api";

const PostsList = () => {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState(null);

  useEffect(() => {
    fetchPosts(currentPage);
  }, [currentPage]);

  const fetchPosts = async (page = 1) => {
    try {
      setLoading(true);
      const response = await postsService.getPosts(page);
      setPosts(response.data.data);
      setPagination(response.data);
      setError("");
    } catch (err) {
      setError("Failed to fetch posts. Please try again.");
      console.error("Error fetching posts:", err);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  };

  if (loading) {
    return (
      <div className="container-fluid px-4 mt-4">
        <div className="text-center">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container-fluid px-4 mt-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Latest Blog Posts</h1>
        <Link to="/create-post" className="btn btn-primary">
          Create New Post
        </Link>
      </div>

      {error && (
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      )}

      {posts.length === 0 ? (
        <div className="text-center">
          <p>No posts available yet.</p>
          <Link to="/create-post" className="btn btn-primary">
            Create the first post
          </Link>
        </div>
      ) : (
        <>
          <div className="row">
            {posts.map((post) => (
              <div key={post.id} className="col-md-6 col-lg-4 mb-4">
                <div className="card h-100">
                  <div className="card-body">
                    <h5 className="card-title">
                      <Link
                        to={`/posts/${post.id}`}
                        className="text-decoration-none"
                      >
                        {post.title}
                      </Link>
                    </h5>
                    <p className="card-text">
                      {post.content.length > 150
                        ? `${post.content.substring(0, 150)}...`
                        : post.content}
                    </p>
                  </div>
                  <div className="card-footer text-muted">
                    <small>
                      By {post.user?.name} on {formatDate(post.created_at)}
                    </small>
                    {post.comments && (
                      <small className="float-end">
                        {post.comments.length} comment
                        {post.comments.length !== 1 ? "s" : ""}
                      </small>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {pagination && pagination.last_page > 1 && (
            <nav aria-label="Posts pagination">
              <ul className="pagination justify-content-center">
                <li
                  className={`page-item ${currentPage === 1 ? "disabled" : ""}`}
                >
                  <button
                    className="page-link"
                    onClick={() => setCurrentPage(currentPage - 1)}
                    disabled={currentPage === 1}
                  >
                    Previous
                  </button>
                </li>

                {[...Array(pagination.last_page)].map((_, index) => (
                  <li
                    key={index + 1}
                    className={`page-item ${
                      currentPage === index + 1 ? "active" : ""
                    }`}
                  >
                    <button
                      className="page-link"
                      onClick={() => setCurrentPage(index + 1)}
                    >
                      {index + 1}
                    </button>
                  </li>
                ))}

                <li
                  className={`page-item ${
                    currentPage === pagination.last_page ? "disabled" : ""
                  }`}
                >
                  <button
                    className="page-link"
                    onClick={() => setCurrentPage(currentPage + 1)}
                    disabled={currentPage === pagination.last_page}
                  >
                    Next
                  </button>
                </li>
              </ul>
            </nav>
          )}
        </>
      )}
    </div>
  );
};

export default PostsList;
